<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Site Key setting - using auth_turnstile as the plugin name
    $settings->add(new admin_setting_configtext(
        'auth_turnstile/site_key',  // matches the config name
        get_string('site_key', 'auth_turnstile'),
        get_string('site_key_desc', 'auth_turnstile'),
        '',
        PARAM_TEXT
    ));

    // Secret Key setting
    $settings->add(new admin_setting_configtext(
        'auth_turnstile/secret_key',  // matches the config name
        get_string('secret_key', 'auth_turnstile'),
        get_string('secret_key_desc', 'auth_turnstile'),
        '',
        PARAM_TEXT
    ));
}
