<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enforced two-factor authentication roles
    |--------------------------------------------------------------------------
    |
    | Users holding one of these roles must enrol TOTP 2FA before they can use
    | the application (the RequireTwoFactor middleware redirects them to the
    | setup page until totp_enabled is true). Set to an empty value to disable
    | the enforcement. The test suite sets this to empty so it stays green.
    |
    */
    'enforce_two_factor_roles' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('SECURITY_ENFORCE_TWO_FACTOR_ROLES', 'super-admin,admin,treasurer'))
    ))),
];
