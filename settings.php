<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Site Key setting
    $settings->add(new admin_setting_configtext(
        'auth_turnstile/site_key',
        get_string('site_key', 'auth_turnstile'),
        get_string('site_key_desc', 'auth_turnstile'),
        '',
        PARAM_TEXT
    ));

    // Admin setting for the Turnstile secret key
    $settings->add(new admin_setting_configtext(
        'auth_turnstile/secret_key',
        get_string('secret_key', 'auth_turnstile'),
        get_string('secret_key_desc', 'auth_turnstile'),
        '',
        PARAM_TEXT
    ));
}
