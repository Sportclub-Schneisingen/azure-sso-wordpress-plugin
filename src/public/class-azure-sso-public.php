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
	 * The authenticator object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Azure_SSO_Authenticator    $authenticator    The authenticator object.
	 */
	private $authenticator;

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

		$this->load_dependencies();
	}

	private function load_dependencies()
	{
		require_once AZURE_SSO_PLUGIN_DIR . 'includes/class-azure-sso-authenticator.php';

		$this->authenticator = new Azure_SSO_Authenticator($this->plugin_name, $this->version);
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
		$login_url = esc_url($login_url);
		$button_text = esc_html(get_option($this->plugin_name . '-option-button-text', __('Log in with Azure AD', $this->plugin_name)));
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-public-login-form.php';
	}

	/**
	 * Receive requests on endpoint.
	 * 
	 * @since    1.0.0
	 * @param    string $template
	 * @return   string
	 */
	public function start_sso($template)
	{
		global $wp_query;
		$query_value = $wp_query->get($this->plugin_name);
		$success = false;

		if (!empty($query_value)) {
			$success = $this->authenticator->request_authorization_code();
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
			//$this->start_login();
			$this->authenticator->request_authorization_code();
		}
	}

	/**
	 * Intercept requests to login page or login process.
	 * 
	 * @since    1.0.0
	 * @param    WP_User|WP_Error $user     WP_User if the user is authenticated. WP_Error or null otherwise.
	 * @param    string           $username Username or email address.
	 * @param    string           $password User password
	 * @return   WP_User|WP_Error
	 */
	public function authenticate($user, $username, $password)
	{
		write_log('===== authenticate =====');

		// Do not re-authenticate if the user is already logged in
		if (is_a($user, 'WP_User')) {
			write_log('===== already logged in =====');
			return $user;
		}

		// Check if the login form was submitted
		if (isset($_POST['wp-submit'])) {
			// Hand back to WordPress to handle the login
			write_log('===== form submitted =====');
			return $user;
		}

		// Check if the login page received the OAuth2 authorization code
		if (isset($_GET['code'])) {
			// TODO: Handle authorization code response
			write_log('===== code received =====');
		}

		// TODO: Check for ID token response

		// To generate login error return WP_Error object
		return $user;
	}

	/**
	 * Build the plugin endpoint URL.
	 * Enforce HTTPS if possible.
	 * 
	 * @since    1.0.0
	 * @param    string $action
	 * @param    bool   $enforce_https
	 * @return   string
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
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/azure-sso-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		// TODO: Remove if not required.
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/azure-sso-public.js', array('jquery'), $this->version, false);
	}
}
