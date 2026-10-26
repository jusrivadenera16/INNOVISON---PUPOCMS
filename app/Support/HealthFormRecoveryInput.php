<?php

namespace App\Support;

use Illuminate\Http\Request;

class HealthFormRecoveryInput
{
    public static function fromRequest(Request $request): array
    {
        $input = $request->input();

        unset(
            $input['_token'],
            $input['_method'],
            $input['digital_signature_data'],
            $input['guardian_signature_data'],
            $input['employee_signature']
        );

        return $input;
    }
}
