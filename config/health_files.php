<?php

return [
    'write_disk' => env('HEALTH_FILES_WRITE_DISK', 'health_private'),
    'legacy_disk' => env('HEALTH_FILES_LEGACY_DISK', 'public'),

    'legacy_fallback' => filter_var(
        env('HEALTH_FILES_LEGACY_FALLBACK', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    'mirror_to_legacy' => filter_var(
        env('HEALTH_FILES_MIRROR_TO_LEGACY', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    'delete_legacy_on_replace' => filter_var(
        env('HEALTH_FILES_DELETE_LEGACY_ON_REPLACE', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    'reference_fields' => [
        'health_profiles' => [
            'student_photo',
            'health_declaration',
            'digital_signature',
            'pwd_id_proof',
            'medical_certificate',
            'chest_xray_result',
            'medical_assessment_upload',
            'clearance_signature_snapshot_path',
        ],
        'health_profile_emp' => [
            'student_photo',
            'health_declaration',
            'medical_certificate',
            'chest_xray_document',
            'pwd_id_proof',
            'uploaded_signature_path',
            'staff_signature',
            'staff_health_form_pdf_path',
        ],
        'health_form_submissions' => [
            'pdf_path',
        ],
        'pending_medical_assessments' => [
            'file_path',
        ],
        'settings' => [
            'clearance_signature_path',
        ],
    ],
];
