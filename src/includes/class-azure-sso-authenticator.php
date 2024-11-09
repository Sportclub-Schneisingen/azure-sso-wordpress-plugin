<?php

/**
 * Externalizes all OAuth2 authentication requests.
 *
 * Sends the request to the authorization and token endpoints.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 */

/**
 * Externalizes all OAuth2 authentication requests.
 *
 * Sends the request to the authorization and token endpoints.
 *
 * @since      1.0.0
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 * @author     Your Name <email@example.com>
 */
class Azure_SSO_Authenticator
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The base URL for login requests.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    BASE_URL    The base URL for login requests.
     */
    private const BASE_URL = 'https://login.microsoftonline.com/';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
    
    /**
     * Request authorization code.
     * Request the authorization code from Microsoft Entra ID.
     *
     * @since    1.0.0
     * @return   bool    True if the request was successful, false otherwise.
     */
    public function request_authorization_code()
    {
        // Check if required configuration values are set
        if (($config = $this->get_config()) === false) {
            return false;
        }

        // Build anti-forgery token
        $antiforgery = bin2hex(random_bytes(64));
        $_SESSION[$this->plugin_name . '-antiforgery'] = $antiforgery;

		// Build query parameters
		$query_params = [
			'client_id'     => $config['client_id'],
			'response_type' => 'code',
			'redirect_uri'  => wp_login_url(),
			'response_mode' => 'query', // TODO: allow for post reponses
			'scope'         => 'openid profile email', // TODO: MS Graph
			'state'         => $antiforgery,
			// TODO: Add support for PKCE
		];
        $url = $this::BASE_URL . urlencode($config['tenant_id']) . '/oauth2/v2.0/authorize';
        wp_redirect(add_query_arg($query_params, $url));
        return true;
    }

    /**
     * Handle authorization code response.
     * 
     * @since    1.0.0
     * @return   WP_Error|string    The authorization code if successful, WP_Error otherwise.
     */
    public function handle_authorization_code_response()
    {
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            return new WP_Error(
                'missing_parameters',
                sprintf(
                    '%s<hr><small>%s</small>',
                    esc_html__('An error occurred during SSO login.', $this->plugin_name),
                    esc_html__('The authorization code or state is missing.', $this->plugin_name),
                )
            );
        }

        if (!isset($_SESSION[$this->plugin_name . '-antiforgery'])) {
            return new WP_Error(
                'missing_parameters',
                sprintf(
                    '%s<hr><small>%s</small>',
                    esc_html__('An error occurred during SSO login.', $this->plugin_name),
                    esc_html__('The nonce is missing.', $this->plugin_name),
                )
            );
        }
        
        $nonce_valid = $_GET['state'] == $_SESSION[$this->plugin_name . '-antiforgery'];
        if (!$nonce_valid) {
            return new WP_Error(
                'nonce_invalid',
                sprintf(
                    '%s<hr><small>%s</small>',
                    esc_html__('An error occurred during SSO login.', $this->plugin_name),
                    esc_html__('The nonce is invalid.', $this->plugin_name),
                )
            );
        }

        return $_GET['code'];
    }

    /**
     * Request ID token.
     * Request the ID token from Microsoft Entra ID.
     * 
     * @since    1.0.0
     * @param    string    $code    The authorization code.
     * @return   array|bool    Array with ID token and access token if successful, false otherwise.
     */
    public function request_id_token($code)
    {
        // Check if required configuration values are set
        if (($config = $this->get_config()) === false) {
            return;
        }
        $client_id = $config['client_id'];
        $client_secret = $config['client_secret'];
        $tenant_id = $config['tenant_id'];

        // Build id token request parameters
        $params = [
            'client_id' => $client_id,
            'scope' => 'openid profile email', // TODO: MS Graph
            'code' => $code,
            'redirect_uri' => wp_login_url(),
            'grant_type' => 'authorization_code',
            'client_secret' => $client_secret,
            // TODO: Add support for PKCE
        ];
        $url = $this::BASE_URL . urlencode($tenant_id) . '/oauth2/v2.0/token';
        $response = wp_remote_post($url, ['body' => $params]);

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return new WP_Error(
                'empty_response',
                sprintf(
                    '%s<hr><small>%s</small>',
                    esc_html__('An error occurred during SSO login.', $this->plugin_name),
                    esc_html__('The response from the server was empty.', $this->plugin_name),
                )
            );
        }

        $body = json_decode($body, true);
        if (isset($body['error'])) {
            $error = esc_html($body['error']);
            $error_desc = esc_html($body['error_description']);
            return new WP_Error(
				$error,
				sprintf(
					'%s<hr><small>%s</small>',
					sprintf('%s (%s)', esc_html__('An error occurred during SSO login.', $this->plugin_name), $error),
					$error_desc,
				)
			);
        }

        // TODO: Validate ID token
        list($header, $payload, $signature) = explode('.', $body['id_token']);
        // if (!$this->validate_id_token($header, $signature, $client_id, $tenant_id)) {
        //     // Invalid ID token
        //     // TODO: Handle invalid token
        //     return false;
        // }

        return [
            'id_token' => $body['id_token'],
            'access_token' => $body['access_token'],
        ];
    }

    /**
     * Sign in user.
     * Sign in the user using the ID token and access token.
     * 
     * @since    1.0.0
     * @param    string    $id_token       The ID token.
     * @param    string    $access_token   The access token.
     * @return   WP_Error|WP_User    The signed in user if successful, a WP_Error otherwise.
     */
    public function sign_in($id_token, $access_token)
    {
        list($header, $payload, $signature) = explode('.', $id_token);
        $payload = json_decode(base64_decode($payload), true);

        $user = get_user_by('email', $payload['email']);
        if ($user === false) {
            return new WP_Error(
                'user_not_found',
                sprintf(
                    '%s<hr><small>%s</small>',
                    esc_html__('Your account has not been registered on this site.', $this->plugin_name),
                    esc_html__('Login was successful, but site did not accept user.', $this->plugin_name),
                )
            );
            // TODO: Create user if not exists
        }

        return $user;
    }

    /**
     * Validate ID token.
     * Validate the ID token received from Microsoft Entra ID.
     * 
     * @since    1.0.0
     * @param    string    $header     The header of the ID token.
     * @param    string    $signature  The signature of the ID token.
     * @param    string    $client_id  The client ID.
     * @param    string    $tenant_id  The tenant ID.
     * @return   bool    True if the ID token is valid, false otherwise.
     */
    private function validate_id_token($header, $signature, $client_id, $tenant_id)
    {
        $openid_config_url = add_query_arg('appid', $client_id, $this::BASE_URL . urlencode($tenant_id) . '/.well-known/openid-configuration');
        $openid_config = wp_remote_retrieve_body(wp_remote_get($openid_config_url));
        $openid_config = json_decode($openid_config, true);
        $jwks_url = $openid_config['jwks_uri'];
        $body = wp_remote_retrieve_body(wp_remote_get($jwks_url));
        $keys = json_decode($body, true)['keys'];

        if (empty($keys)) {
            return false;
        }

        $header = json_decode(base64_decode($header), true);
        $kid = $header['kid'];
        $alg = $header['alg'];
        $key = null;
        foreach ($keys as $k) {
            if ($k['kid'] == $kid) {
                $key = $k;
                break;
            }
        }

        if ($key === null) {
            // No matching key found
            return false;
        }

        $x509 = '-----BEGIN CERTIFICATE-----' . PHP_EOL . $key['x5c'][0] . PHP_EOL . '-----END CERTIFICATE-----';
    }

    /**
     * Get configuration values, if the plugin is configured.
     * 
     * @since    1.0.0
     * @return   bool|array    False if not configured, array of configuration values if configured.
     */
    private function get_config()
    {
        $client_id = get_option($this->plugin_name)['client_id'];
        $client_secret = get_option($this->plugin_name)['client_secret'];
        $tenant_id = get_option($this->plugin_name)['tenant_id'];

        if (empty($client_id) || empty($client_secret) || empty($tenant_id)) {
            $options_page = add_query_arg('page', $this->plugin_name, admin_url('options-general.php'));
            $login_url = add_query_arg($this->plugin_name . '-no-redirect', '', wp_login_url($options_page));
            wp_die(
                esc_html__('Azure SSO is not configured correctly. Check configuration and try again.', $this->plugin_name),
                esc_html__('Azure SSO Error', $this->plugin_name),
                [
                    'response' => 500,
                    'back_link' => true,
                    'link_url' => $login_url,
                    'link_text' => esc_html__('Configure Azure SSO', $this->plugin_name),
                ]
            );
            return false;
        } else {
            return [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'tenant_id' => $tenant_id,
            ];
        }
    }
}
