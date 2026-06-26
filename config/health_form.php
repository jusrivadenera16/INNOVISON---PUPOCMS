<?php

return [
    'allow_local_test_references' => env('HEALTH_FORM_ALLOW_LOCAL_TEST_REFERENCES', false),

    'local_test_references' => array_values(array_filter(array_map(
        static fn ($reference) => strtoupper(trim((string) $reference)),
        explode(',', env('HEALTH_FORM_LOCAL_TEST_REFERENCES', 'TEST-LOCAL-0001,TEST-LOCAL-0002,TEST-LOCAL-0003'))
    ))),
];
