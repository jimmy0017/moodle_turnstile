<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Admin setting for the Turnstile site key
    $settings->add(new admin_setting_configtext(
        'auth_turnstile/sitekey',
        get_string('turnstilesitekey', 'auth_turnstile'),
        get_string('turnstilesitekey_desc', 'auth_turnstile'),
        '',
        PARAM_TEXT
    ));

    // Admin setting for the Turnstile secret key
    $settings->add(new admin_setting_configtext(
        'auth_turnstile/secretkey',
        get_string('turnstilesecretkey', 'auth_turnstile'),
        get_string('turnstilesecretkey_desc', 'auth_turnstile'),
        '',
        PARAM_TEXT
    ));
}
