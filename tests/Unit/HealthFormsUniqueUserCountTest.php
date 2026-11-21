<?php

namespace Tests\Unit;

use App\Http\Controllers\ReportsController;
use App\Models\HealthProfile;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class HealthFormsUniqueUserCountTest extends TestCase
{
    public function test_export_total_counts_each_person_once_and_keeps_unlinked_records_distinct(): void
    {
        $firstForm = new HealthProfile();
        $firstForm->setRawAttributes(['id' => 10, 'user_id' => 63], true);

        $secondForm = new HealthProfile();
        $secondForm->setRawAttributes(['id' => 11, 'user_id' => 63], true);

        $anotherPerson = new HealthProfile();
        $anotherPerson->setRawAttributes(['id' => 12, 'user_id' => 64], true);

        $unlinkedForm = new HealthProfile();
        $unlinkedForm->setRawAttributes(['id' => 13, 'user_id' => null], true);

        $method = new ReflectionMethod(ReportsController::class, 'uniqueHealthFormsUserCount');
        $count = $method->invoke(new ReportsController(), collect([
            $firstForm,
            $secondForm,
            $anotherPerson,
            $unlinkedForm,
        ]));

        $this->assertSame(3, $count);
    }
}
