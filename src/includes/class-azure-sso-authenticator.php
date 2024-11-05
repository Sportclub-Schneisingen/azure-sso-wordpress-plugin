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
     */
    private $base_url = 'https://login.microsoftonline.com/';

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
        $client_id = get_option($this->plugin_name . '-option-client-id', '');
		$client_secret = get_option($this->plugin_name . '-option-client-secret', '');
		$tenant_id = get_option($this->plugin_name . '-option-tenant-id', '');

		// Check if required configuration values are set
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
		}

		// Build state data
		$state_data = array();
		if (isset($_GET['redirect_to'])) {
			$state_data['redirect_to'] = esc_url_raw($_GET['redirect_to']);
			$state_data['nonce'] = wp_create_nonce($this->plugin_name . '_' . $state_data['redirect_to']);
		} else {
			$state_data['nonce'] = wp_create_nonce($this->plugin_name);
		}

		// Build query parameters
		$query_params = [
			'client_id'     => $client_id,
			'response_type' => 'code',
			'redirect_uri'  => wp_login_url(), //TODO: $this->build_endpoint_url('callback'),
			'response_mode' => 'query', // TODO: allow for post reponses
			'scope'         => 'openid profile email', // TODO: MS Graph
			'state'         => json_encode($state_data),
			// TODO: Add support for PKCE
		];
        $url = $this->base_url . urlencode($tenant_id) . '/oauth2/v2.0/authorize';
        wp_redirect(add_query_arg($query_params, $url));
        return true;
    }

    /**
     * Request ID token.
     * Request the ID token from Microsoft Entra ID.
     * 
     * @since    1.0.0
     * @param    string    $code    The authorization code.
     * @return   bool    True if the request was successful, false otherwise.
     */
    public function request_id_token($code)
    {
        $client_id = get_option($this->plugin_name . '-option-client-id', '');
        $client_secret = get_option($this->plugin_name . '-option-client-secret', '');
        $tenant_id = get_option($this->plugin_name . '-option-tenant-id', '');

        // Check if required configuration values are set
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
        }

        // Build parameters
        $params = [
            'client_id' => $client_id,
            'scope' => 'openid profile email', // TODO: MS Graph
            'code' => $code,
            'redirect_uri' => wp_login_url(), //TODO: $this->build_endpoint_url('callback'),
            'grant_type' => 'authorization_code',
            'client_secret' => $client_secret,
            // TODO: Add support for PKCE
        ];
        $url = $this->base_url . urlencode($tenant_id) . '/oauth2/v2.0/token';
        $$response = wp_remote_post($url, ['body' => $params]);

        // TODO: Process response
        write_log($response);
        return true;
    }
}
