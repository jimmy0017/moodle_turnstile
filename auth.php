<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_turnstile extends auth_plugin_base {
    /**
     * Constructor
     */
    public function __construct() {
        $this->authtype = 'turnstile';
        $this->config = get_config('auth_turnstile');
    }

    /**
     * Hook called when user attempts to login.
     * 
     * @param string $username The username.
     * @param string $password The password.
     * @return bool True if authentication is successful.
     */
    public function user_login($username, $password) {
        global $CFG;

        // 1. Verify the Turnstile token from the login form submission
        //    The token is typically in $_POST['cf-turnstile-response'] or similar key.
        $tokenKey = optional_param('cf-turnstile-response', '', PARAM_RAW_TRIMMED);
        if (empty($tokenKey)) {
            // No Turnstile token => fail the login immediately
            return false;
        }

        $secret = trim($this->config->secretkey);
        if (!$this->verify_turnstile_token($tokenKey, $secret)) {
            return false;
        }

        // 2. If Turnstile verification is successful, proceed with normal Moodle login checks.
        //    Usually we'd use Moodle's internal method to check $username / $password 
        //    against the 'manual' or 'db' authentication or otherwise.
        //    For example, if we want to delegate to the 'manual' auth plugin:

        $manualauth = get_auth_plugin('manual');
        if ($manualauth->user_login($username, $password)) {
            return true;
        }

        // If delegation or manual check fails, return false
        return false;
    }

    /**
     * Verifies the Turnstile token via Cloudflare endpoint.
     *
     * @param string $tokenKey
     * @param string $secretKey
     * @return bool True if verification is successful.
     */
    private function verify_turnstile_token($tokenKey, $secretKey) {
        $url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
        
        $postdata = [
            'secret' => $secretKey,
            'response' => $tokenKey
        ];

        $curl = new curl(); // Moodle's built-in Curl class
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_POSTFIELDS' => http_build_query($postdata),
        ];

        $response = $curl->post($url, $postdata, $options);
        $data = json_decode($response);

        // The response from Cloudflare Turnstile typically includes: {"success":true/false, ...}
        if (isset($data->success) && $data->success === true) {
            return true;
        }
        return false;
    }

    /**
     * Hook called during new user creation (self-registration).
     * Only relevant if your plugin handles user creation.
     *
     * @param stdClass $user The user object from signup form
     * @param bool $notify Whether to notify user via email
     * @return int User ID
     */
    public function user_signup($user, $notify=true) {
        // This method is triggered if your plugin is set up as the self-registration method.
        // 1. Check Turnstile token from the signup form
        $tokenKey = optional_param('cf-turnstile-response', '', PARAM_RAW_TRIMMED);
        $secret   = trim($this->config->secretkey);

        if (empty($tokenKey) || !$this->verify_turnstile_token($tokenKey, $secret)) {
            throw new moodle_exception('turnstileverificationfailed', 'auth_turnstile');
        }

        // 2. If Turnstile passes, proceed with creating the user via Moodle’s core API
        return create_user_record($user->username, $user->password, 'turnstile');
    }

    /**
     * Returns the authentication plugin title (displayed in admin plugins list).
     */
    public function get_title() {
        return get_string('pluginname', 'auth_turnstile');
    }

    public function loginpage_hook() {
        global $OUTPUT, $PAGE;
        
        // Add Turnstile script to page header
        $PAGE->requires->js(new moodle_url('https://challenges.cloudflare.com/turnstile/v0/api.js'), true);
        
        // Render the turnstile widget
        $templatecontext = [
            'sitekey' => $this->config->site_key,
            'theme' => $PAGE->theme->name === 'dark' ? 'dark' : 'light'
        ];
        
        return $OUTPUT->render_from_template('auth_turnstile/turnstile', $templatecontext);
    }
}
