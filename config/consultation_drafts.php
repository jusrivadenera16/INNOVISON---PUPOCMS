<?php

return [
    'retention_days' => max(0, (int) env('CONSULTATION_DRAFT_RETENTION_DAYS', 1)),
];
