<?php

namespace Tests\Unit;

use App\Models\HealthProfile;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class HealthProfileEncryptionTransitionTest extends TestCase
{
    public function test_enabled_transition_writes_and_reads_encrypted_mirrors(): void
    {
        Config::set('health_data_encryption.enabled', true);
        Config::set('health_data_encryption.key', 'base64:' . base64_encode(str_repeat('h', 32)));

        $profile = new HealthProfile();
        $profile->medical_history = ['Asthma'];
        $profile->other_illness = 'None';
        $profile->food_allergies = 'Peanuts';
        $profile->medicine_allergies = ['Aspirin'];
        $profile->other_med_allergies = 'None';
        $profile->vaccine_history = ['brand' => 'Example'];

        $this->assertNull($profile->getAttributes()['medical_history']);
        $this->assertNull($profile->getAttributes()['food_allergies']);
        $this->assertNotNull($profile->getAttributes()['medical_history_encrypted']);
        $this->assertNotNull($profile->getAttributes()['food_allergies_encrypted']);
        $this->assertSame(['Asthma'], $profile->medical_history);
        $this->assertSame('None', $profile->other_illness);
        $this->assertSame('Peanuts', $profile->food_allergies);
        $this->assertSame(['Aspirin'], $profile->medicine_allergies);
        $this->assertSame('None', $profile->other_med_allergies);
        $this->assertSame(['brand' => 'Example'], $profile->vaccine_history);
    }

    public function test_enabled_transition_falls_back_to_plaintext_when_mirror_is_missing(): void
    {
        Config::set('health_data_encryption.enabled', true);
        Config::set('health_data_encryption.key', 'base64:' . base64_encode(str_repeat('h', 32)));

        $profile = new HealthProfile();
        $profile->setRawAttributes([
            'medical_history' => json_encode(['Asthma']),
        ], true);

        $this->assertSame(['Asthma'], $profile->medical_history);
    }

    public function test_disabled_transition_does_not_write_mirrors(): void
    {
        Config::set('health_data_encryption.enabled', false);

        $profile = new HealthProfile();
        $profile->medical_history = ['Asthma'];

        $this->assertNull($profile->getRawOriginal('medical_history_encrypted'));
        $this->assertSame(['Asthma'], $profile->medical_history);
    }

    public function test_referral_details_are_encrypted_in_the_single_referral_column(): void
    {
        Config::set('health_data_encryption.enabled', true);
        Config::set('health_data_encryption.key', 'base64:' . base64_encode(str_repeat('r', 32)));

        $details = [
            'required' => true,
            'referred_to' => 'Cardiology',
            'notes' => 'Follow up within two weeks.',
        ];
        $profile = new HealthProfile();
        $profile->referral_details = $details;
        $storedValue = $profile->getAttributes()['referral_details'];

        $this->assertIsString($storedValue);
        $this->assertStringNotContainsString('Cardiology', $storedValue);
        $this->assertStringNotContainsString('Follow up within two weeks.', $storedValue);
        $this->assertSame($details, $profile->referral_details);
        $this->assertArrayNotHasKey('referral_details_encrypted', $profile->getAttributes());
    }
}
