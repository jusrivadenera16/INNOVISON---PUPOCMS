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

        $profile = new HealthProfile();
        $profile->medical_history = ['Asthma'];
        $profile->other_illness = 'None';
        $profile->food_allergies = 'Peanuts';
        $profile->medicine_allergies = ['Aspirin'];
        $profile->other_med_allergies = 'None';
        $profile->vaccine_history = ['brand' => 'Example'];

        $this->assertNotSame('Asthma', $profile->getRawOriginal('medical_history_encrypted'));
        $this->assertNotSame('Peanuts', $profile->getRawOriginal('food_allergies_encrypted'));
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
}
