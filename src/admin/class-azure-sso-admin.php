<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin
 * @author     Your Name <email@example.com>
 */
class Azure_SSO_Admin
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
	 * The options for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The options for this plugin.
	 */
	private $options;

	/**
	 * The default options for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $defaults    The default options for this plugin.
	 */
	private $defaults;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->defaults = [
			'client_id'     => '',
			'client_secret' => '',
			'tenant_id'     => '',
			'button_text'   => __('Sign in with Microsoft Entra ID', $this->plugin_name),
			'use_post'      => false,
			'auto_redirect' => false,
		];
	}

	/**
	 * Adds options page link to the admin menu (submenu of the settings menu).
	 * 
	 * @since    1.0.0
	 */
	public function add_menu()
	{
		add_options_page(
			__('Azure SSO Settings', $this->plugin_name),
			__('Azure SSO', $this->plugin_name),
			'manage_options',
			$this->plugin_name,
			array($this, 'options_page'),
		);
	}

	/**
	 * Display admin notice if the plugin is not fully configured.
	 * 
	 * @since    1.0.0
	 */
	public function display_notices()
	{
		$client_id = isset($this->options['client_id']) ? $this->options['client_id'] : '';
		$client_secret = isset($this->options['client_secret']) ? $this->options['client_secret'] : '';
		$tenant_id = isset($this->options['tenant_id']) ? $this->options['tenant_id'] : '';

		if (empty($client_id) || empty($client_secret) || empty($tenant_id)) {
			add_action('admin_notices', function() {
				?>
				<div class="notice notice-info is-dismissible">
					<p><?php _e('Azure SSO is not fully configured. SSO will not work.', $this->plugin_name); ?></p>
					<p><a href="<?php echo esc_url($this->options_page_url()); ?>" class="button button-primary"><?php _e('Configure Azure SSO', $this->plugin_name); ?></a></p>
				</div>
				<?php
			});
		}
	}


	/**
	 * Add settings link to the plugin page.
	 * 
	 * @since    1.0.0
	 * @param    array    $links    The existing links array.
	 * @return   array    The modified links array.
	 */
	public function link_settings($links) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url($this->options_page_url()),
			esc_html__('Settings', $this->plugin_name)
		);
		array_push($links, $settings_link);
		return $links;
	}

	/**
	 * Create the options page.
	 * 
	 * @since    1.0.0
	 */
	public function options_page()
	{
		$this->options = wp_parse_args(get_option($this->plugin_name, []), $this->defaults);
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-display.php';
	}

	/**
	 * Register settings, sections and fields.
	 * Use one option array for all settings of the plugin.
	 * 
	 * @since    1.0.0
	 */
	public function register_settings()
	{
		register_setting(
			$this->plugin_name,
			$this->plugin_name,
			array($this, 'sanitize_options')
		);

		add_settings_section(
			$this->plugin_name . '-section-general',
			__('General Options', $this->plugin_name),
			function () {
				echo sprintf(
					/* translators: %s: Login URL */
					__('Configure client ID, client secret and tenant ID.
						Get them from your Microsoft Entra ID app registration.<br>
						Add <code>%s</code> as the redirect URL to the app registration.', $this->plugin_name),
					esc_url(wp_login_url())
				);
			}, 
			$this->plugin_name,
		);

		add_settings_section(
			$this->plugin_name . '-section-login',
			__('Login Page Options', $this->plugin_name),
			function () {
				_e('Configure the login page behavior or style the login page button.', $this->plugin_name);
			},
			$this->plugin_name,
		);

		add_settings_section(
			$this->plugin_name . '-section-advanced',
			__('Advanced Options', $this->plugin_name),
			function () {
				_e('Configure advanced options for SSO here.', $this->plugin_name);
			},
			$this->plugin_name,
		);

		add_settings_field(
			'client_id',
			__('Client ID', $this->plugin_name),
			array($this, 'text_field'),
			$this->plugin_name,
			$this->plugin_name . '-section-general',
			[
				'id'        => 'client_id',
				'label_for' => 'client_id',
			],
		);

		add_settings_field(
			'client_secret',
			__('Client Secret', $this->plugin_name),
			array($this, 'password_field'), // TODO: Encrypt content
			$this->plugin_name,
			$this->plugin_name . '-section-general',
			[
				'id'        => 'client_secret',
				'label_for' => 'client_secret',
			],
		);

		add_settings_field(
			'tenant_id',
			__('Tenant ID', $this->plugin_name),
			array($this, 'text_field'),
			$this->plugin_name,
			$this->plugin_name . '-section-general',
			[
				'id'        => 'tenant_id',
				'label_for' => 'tenant_id',
			],
		);

		add_settings_field(
			'button_text',
			__('Login Button Text', $this->plugin_name),
			array($this, 'text_field'),
			$this->plugin_name,
			$this->plugin_name . '-section-login',
			[
				'id'        => 'button_text',
				'label_for' => 'button_text',
			],
		);

		add_settings_field(
			'use_post',
			__('Use POST Requests', $this->plugin_name),
			array($this, 'checkbox'),
			$this->plugin_name,
			$this->plugin_name . '-section-advanced',
			[
				'id'        => 'use_post',
				'label_for' => 'use_post',
			],
		);

		add_settings_field(
			'auto_redirect',
			__('Auto Redirect', $this->plugin_name),
			array($this, 'checkbox'),
			$this->plugin_name,
			$this->plugin_name . '-section-advanced',
			[
				'id'        => 'auto_redirect',
				'label_for' => 'auto_redirect',
			],
		);
	}

	/**
	 * Sanitize the options.
	 * 
	 * @since    1.0.0
	 * @param    array    $input    The input options.
	 * @return   array    The sanitized options.
	 */
	public function sanitize_options($input)
	{
		$sanitized_input = $this->options;
		if (empty($input)) {
			return $sanitized_input; // Unchanged
		}

		foreach ($input as $key => $value) {
			// TODO: Sanitize different inputs accordingly
			$sanitized_input[$key] = sanitize_text_field($value);
		}
		return $sanitized_input;
	}

	/**
	 * Get the options page URL.
	 * 
	 * @since    1.0.0
	 * @return   string
	 */
	private function options_page_url()
	{
		return add_query_arg('page', $this->plugin_name, admin_url('options-general.php'));
	}

	/**
	 * Render a text field for an option.
	 * 
	 * @since    1.0.0
	 */
	public function text_field($args)
	{
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-text-field.php';
	}

	/**
	 * Render a password field for an option.
	 * 
	 * @since    1.0.0
	 */
	public function password_field($args)
	{
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-password-field.php';
	}

	/**
	 * Render a text area for an option.
	 * 
	 * @since    1.0.0
	 */
	public function text_area($args)
	{
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-text-area.php';
	}

	/**
	 * Render a checkbox for an option.
	 * 
	 * @since    1.0.0
	 */
	public function checkbox($args)
	{
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-checkbox.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		// TODO: Remove if not required
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/azure-sso-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		// TODO: Remove if not required
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/azure-sso-admin.js', array('jquery'), $this->version, false);
	}
}
