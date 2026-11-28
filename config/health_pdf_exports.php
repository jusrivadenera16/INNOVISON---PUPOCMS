<?php

return [
    // Deliberately separate from QUEUE_CONNECTION: never render a large report in the web request.
    // Deployment: install the locked Composer dependencies, run the scoped PDF export migration,
    // refresh cached configuration, and run schedule:run every minute (or the health_pdf worker).
    // APP_KEY protects filters/manifests; the existing health-file key protects PDF files.
    'scan_size' => 100,
    'batch_size' => 25,
    'ttl_minutes' => 360,
];
