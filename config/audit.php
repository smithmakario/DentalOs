<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Alert Email
    |--------------------------------------------------------------------------
    |
    | Optional fallback address when no super admin users exist. Super admins
    | always receive in-app mail notifications for high-priority audit events.
    |
    */

    'alert_email' => env('AUDIT_ALERT_EMAIL'),

];
