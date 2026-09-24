<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deployment Panel
    |--------------------------------------------------------------------------
    |
    | Web replacement for the former standalone deploy-helper.php file.
    | Access is guarded by session authentication (Fortify), the verified
    | middleware and the EnsureAdministrator middleware, so no shared
    | secret is needed. Set DEPLOYMENT_PANEL_ENABLED=false to disable
    | the panel entirely without deploying new code.
    |
    */

    'enabled' => (bool) env('DEPLOYMENT_PANEL_ENABLED', true),

];
