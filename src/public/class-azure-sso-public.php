<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/public
 * @author     Your Name <email@example.com>
 */
class Azure_SSO_Public
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
	 * Register endpoint.
	 * 
	 * @since    1.0.0
	 */
	public function add_rewrite()
	{
		add_rewrite_endpoint($this->plugin_name, EP_ROOT);
	}

	/**
	 * Register the stylesheet on the login page.
	 * 
	 * @since    1.0.0
	 */
	public function add_stylesheet_to_login_page()
	{
		echo '<link rel="stylesheet" type="text/css" href="' . plugin_dir_url(__FILE__) . 'css/azure-sso-public.css" />';
	}

	/**
	 * Show the SSO login form.
	 * 
	 * @since    1.0.0
	 */
	public function show_login_form()
	{
		$login_url = $this->build_endpoint_url('start');
		if (isset($_REQUEST['redirect_to'])) {
			$login_url = add_query_arg('redirect_to', $_REQUEST['redirect_to'], $login_url);
		}
		$button_text = get_option($this->plugin_name . '-option-button-text', __('Log in with Azure AD', $this->plugin_name));
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-public-login-form.php';
	}

	/**
	 * Receive requests on endpoint.
	 * 
	 * @since    1.0.0
	 */
	public function handle_callbacks($template)
	{
		global $wp_query;

		$query_value = $wp_query->get($this->plugin_name);
		$success = false;

		if ($query_value == 'start') {
			$success = $this->start_login();
		} elseif ($query_value == 'callback') {
			$success = $this->handle_callback();
		} else {
			return $template;
		}

		if ($success) {
			return $template;
		} else {
			exit;
		}
	}

	/**
	 * Automatically redirect to SSO login.
	 * 
	 * @since    1.0.0
	 */
	public function auto_redirect_to_sso()
	{
		// Check if auto-redirect is enabled, allow override using 'azure-sso-auto-redirect' filter
		$auto_redirect = apply_filters(
			$this->plugin_name . '-auto-redirect',
			get_option($this->plugin_name . '-option-auto-redirect', false)
		);

		// Check for anti-lockout query parameter
		$auto_redirect = $auto_redirect && !isset($_GET[$this->plugin_name . '-no-redirect']);

		// Do not auto-redirect if user is trying to log in to prevent infinite loops
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
		$action = isset($_GET['loggedout']) ? 'loggedout' : $action;
		$auto_redirect = $auto_redirect && ($action == 'login');
		
		// Prevent making the login form unusable
		$auto_redirect = $auto_redirect && !isset($_GET['code']) && !isset($_POST['log']);

		if ($auto_redirect) {
			$this->start_login();
		}
	}

	/**
	 * Start the login process.
	 * 
	 * @since    1.0.0
	 */
	private function start_login()
	{
		$login_url = $this->build_login_url();
		return wp_redirect($login_url);
	}

	/**
	 * Handle callback from identity provider.
	 * 
	 * @since    1.0.0
	 */
	private function handle_callback()
	{
		// TODO: Handle callback/sign in users
	}

	/**
	 * Build the login URL.
	 * 
	 * @since    1.0.0
	 */
	private function build_login_url()
	{
		$client_id = get_option($this->plugin_name . '-option-client-id', '');
		$client_secret = get_option($this->plugin_name . '-option-client-secret', '');
		$tenant_id = get_option($this->plugin_name . '-option-tenant-id', '');

		// Check if required configuration values are set
		if ($client_id == '' || $client_secret == '' || $tenant_id == '') {
			$options_page = add_query_arg('page', $this->plugin_name, admin_url('options-general.php'));
			$login_url = add_query_arg($this->plugin_name . '-no-redirect', '', wp_login_url($options_page));
			wp_die(
				__('Azure SSO is not configured correctly. Check configuration and try again.', $this->plugin_name),
				__('Azure SSO Error', $this->plugin_name),
				[
					'response' => 500,
					'back_link' => true,
					'link_url' => $login_url,
					'link_text' => __('Configure Azure SSO', $this->plugin_name),
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

		// Build url
		$base_url = 'https://login.microsoftonline.com/' . urlencode($tenant_id) . '/oauth2/v2.0/authorize';
		$query_params = [
			'client_id'     => $client_id,
			'response_type' => 'code',
			'redirect_uri'  => $this->build_endpoint_url('callback'),
			'response_mode' => 'query', // TODO: allow for post reponses
			'scope'         => 'openid profile email', // TODO MS Graph
			'state'         => json_encode($state_data),
		];
		
		return $base_url . '?' . http_build_query($query_params);
	}

	/**
	 * Build the endpoint URL. Enforce HTTPS if required/possible.
	 * 
	 * @since    1.0.0
	 */
	private function build_endpoint_url($action, $enforce_https = false)
	{
		// TODO: Use rewrites if enabled
		$url = site_url('?' . $this->plugin_name . '=' . urlencode($action), $enforce_https ? 'https' : null);
		$host = parse_url($url, PHP_URL_HOST);
		if ($action === 'callback' && $enforce_https === false && in_array($host, ['localhost', '127.0.0.1',], true)) {
			return $this->build_endpoint_url($action, true);
		} else {
			return $url;
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Azure_SSO_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Azure_SSO_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/azure-sso-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Azure_SSO_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Azure_SSO_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/azure-sso-public.js', array('jquery'), $this->version, false);
	}
}
