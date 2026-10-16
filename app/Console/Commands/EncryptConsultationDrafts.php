<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EncryptConsultationDrafts extends Command
{
    protected $signature = 'consultation-drafts:encrypt {--apply : Encrypt draft payloads and clear their legacy plaintext JSON}';

    protected $description = 'Verify and encrypt consultation draft payloads before clearing their legacy JSON copies.';

    public function handle(): int
    {
        if (!Schema::hasTable('consultation_drafts') || !Schema::hasColumn('consultation_drafts', 'payload_encrypted')) {
            $this->error('Run the consultation-draft encrypted-payload migration before this command.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $verified = 0;
        $updated = 0;
        $unverified = 0;

        DB::table('consultation_drafts')->orderBy('id')->chunkById(100, function ($drafts) use (
            $apply,
            &$verified,
            &$updated,
            &$unverified
        ): void {
            foreach ($drafts as $draft) {
                $legacyPayload = json_decode((string) $draft->payload, true);
                if (!is_array($legacyPayload)) {
                    $unverified++;
                    continue;
                }

                try {
                    $encryptedPayload = is_string($draft->payload_encrypted ?? null)
                        ? Crypt::decrypt($draft->payload_encrypted)
                        : null;
                } catch (Throwable $exception) {
                    $unverified++;
                    continue;
                }

                $hasEncryptedPayload = is_array($encryptedPayload);
                if ($hasEncryptedPayload && $legacyPayload !== [] && $encryptedPayload !== $legacyPayload) {
                    $unverified++;
                    continue;
                }

                if ($hasEncryptedPayload && $legacyPayload === []) {
                    $verified++;
                    continue;
                }

                if (!$hasEncryptedPayload) {
                    $encryptedPayload = $legacyPayload;
                }

                if (!$apply) {
                    $verified++;
                    continue;
                }

                try {
                    $wasUpdated = DB::transaction(function () use ($draft, $legacyPayload, $encryptedPayload, $hasEncryptedPayload): bool {
                        $current = DB::table('consultation_drafts')
                            ->where('id', $draft->id)
                            ->lockForUpdate()
                            ->first();

                        if (!$current || (string) $current->payload !== (string) $draft->payload) {
                            return false;
                        }

                        $currentLegacyPayload = json_decode((string) $current->payload, true);
                        if (!is_array($currentLegacyPayload) || $currentLegacyPayload !== $legacyPayload) {
                            return false;
                        }

                        $ciphertext = $hasEncryptedPayload
                            ? $current->payload_encrypted
                            : Crypt::encrypt($encryptedPayload);
                        $decrypted = Crypt::decrypt($ciphertext);
                        if ($decrypted !== $encryptedPayload) {
                            return false;
                        }

                        return DB::table('consultation_drafts')
                            ->where('id', $draft->id)
                            ->update([
                                'payload_encrypted' => $ciphertext,
                                'payload' => json_encode([], JSON_THROW_ON_ERROR),
                            ]) === 1;
                    });
                } catch (Throwable $exception) {
                    $wasUpdated = false;
                }

                if (!$wasUpdated) {
                    $unverified++;
                    continue;
                }

                $verified++;
                $updated++;
            }
        });

        $this->info(sprintf(
            '%s: %d draft(s) verified; %d plaintext payload(s) cleared; %d draft(s) not verified.',
            $apply ? 'Apply result' : 'Dry-run result',
            $verified,
            $updated,
            $unverified
        ));

        if (!$apply) {
            $this->warn('Dry run only. No data was changed. Confirm a verified backup before using --apply.');
        }

        return $unverified === 0 ? self::SUCCESS : self::FAILURE;
    }
}
