<?php

namespace Tests\Unit;

use App\Http\Controllers\WalkInController;
use App\Models\HealthProfile;
use App\Models\User;
use ReflectionMethod;
use Tests\TestCase;

class WalkInHealthProfileInformationMappingTest extends TestCase
{
    public function test_it_maps_legacy_conditions_to_student_health_form_values(): void
    {
        $mapping = $this->normalize([
            'Tuberculosis',
            'Primary Complex',
            'Asthma',
            'Others',
        ], '');

        $this->assertSame([
            'conditions' => ['Tuberculosis / Primary Complex', 'Asthma'],
            'other_illness' => 'Others',
        ], $mapping);
    }

    public function test_it_keeps_existing_other_illness_notes(): void
    {
        $mapping = $this->normalize(['Others'], 'Follow-up notes');

        $this->assertSame([], $mapping['conditions']);
        $this->assertSame('Follow-up notes', $mapping['other_illness']);
    }

    public function test_editor_payload_uses_the_student_health_form_condition_values(): void
    {
        $profile = new HealthProfile();
        $profile->medical_history = ['Tuberculosis', 'Primary Complex', 'Others'];

        $method = new ReflectionMethod(new WalkInController(), 'healthProfileInformationPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new WalkInController(), $profile, new User(['name' => 'Test Student']));

        $this->assertSame(
            'Tuberculosis / Primary Complex',
            $payload['medical_history']['medical_history']
        );
        $this->assertSame('Others', $payload['medical_history']['other_illness']);
    }

    private function normalize(array $conditions, string $otherIllness): array
    {
        $method = new ReflectionMethod(new WalkInController(), 'normalizeHealthProfileMedicalHistory');
        $method->setAccessible(true);

        return $method->invoke(new WalkInController(), $conditions, $otherIllness);
    }
}
