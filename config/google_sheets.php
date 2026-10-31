
<?php

return [
    // URL Web App dari deployment Google Apps Script.
    'webhook_url' => env('GOOGLE_SHEETS_WEBHOOK_URL', ''),

    // Harus sama dengan Script Property WEBHOOK_SECRET.
    'webhook_secret' => env('GOOGLE_SHEETS_WEBHOOK_SECRET', ''),
];
