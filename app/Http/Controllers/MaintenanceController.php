<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

class MaintenanceController extends Controller
{
    public function show()
    {
        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $maintenanceEnabled = Schema::hasTable('system_settings')
            && SystemSetting::booleanValue('maintenance_mode_enabled', false);

        if (!$maintenanceEnabled) {
            return redirect()->route('landing')->withHeaders($headers);
        }

        $estimatedCompletion = SystemSetting::getValue('maintenance_estimated_completion', null);
        $lastUpdated = SystemSetting::getValue('maintenance_last_updated', null);

        return response()
            ->view('maintenance', compact('estimatedCompletion', 'lastUpdated'))
            ->withHeaders($headers);
    }
}
