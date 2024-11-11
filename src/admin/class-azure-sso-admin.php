<?php

/**
 * The admin-specific functionality of the plugin.
 *
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
 * @author     Janick Lehmann <j.clehmann@hotmail.com>
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

		if (get_option($this->plugin_name) === false) {
			$defaults = [
				'client_id'     => '',
				'client_secret' => '',
				'tenant_id'     => '',
				'button_text'   => __('Sign in with Microsoft Entra ID', $this->plugin_name),
				'auto_redirect' => false,
				'create_user'   => false,
				'use_post'      => false,
			];
			add_option($this->plugin_name, $defaults);
		}
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
		$options = get_option($this->plugin_name);
		$client_id = $options['client_id'];
		$client_secret = $options['client_secret'];
		$tenant_id = $options['tenant_id'];

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
	 * @return   array              The modified links array.
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
				'label_for' => 'button_text',
			],
		);

		add_settings_field(
			'auto_redirect',
			__('Auto Redirect', $this->plugin_name),
			array($this, 'checkbox'),
			$this->plugin_name,
			$this->plugin_name . '-section-login',
			[
				'label_for' => 'auto_redirect',
			],
		);

		add_settings_field(
			'create_user',
			__('Create User', $this->plugin_name),
			array($this, 'checkbox'),
			$this->plugin_name,
			$this->plugin_name . '-section-advanced',
			[
				'label_for' => 'create_user',
			],
		);

		add_settings_field(
			'use_post',
			__('Use POST Requests', $this->plugin_name),
			array($this, 'checkbox'),
			$this->plugin_name,
			$this->plugin_name . '-section-advanced',
			[
				'label_for' => 'use_post',
			],
		);
	}

	/**
	 * Sanitize the options.
	 * 
	 * @since    1.0.0
	 * @param    array    $input    The input options.
	 * @return   array              The sanitized options.
	 */
	public function sanitize_options($input)
	{
		$output = [];
		foreach ($input as $key => $value) {
			if (!isset($input[$key])) {
				continue;
			}
			// TODO: Sanitize different inputs accordingly
			$output[$key] = sanitize_text_field($value);
		}
		return $output;
	}

	/**
	 * Get the options page URL.
	 * 
	 * @since    1.0.0
	 * @return   string    The options page URL.
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
		$options = get_option($this->plugin_name);
		$id = esc_attr($args['label_for']);
		$name = sprintf('%s[%s]', esc_attr($this->plugin_name), $args['label_for']);
		$value = $options[$args['label_for']];
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-text-field.php';
	}

	/**
	 * Render a password field for an option.
	 * 
	 * @since    1.0.0
	 */
	public function password_field($args)
	{
		$options = get_option($this->plugin_name);
		$id = esc_attr($args['label_for']);
		$name = sprintf('%s[%s]', esc_attr($this->plugin_name), $args['label_for']);
		$value = $options[$args['label_for']];
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-password-field.php';
	}

	/**
	 * Render a text area for an option.
	 * 
	 * @since    1.0.0
	 */
	public function text_area($args)
	{
		$options = get_option($this->plugin_name);
		$id = esc_attr($args['label_for']);
		$name = sprintf('%s[%s]', esc_attr($this->plugin_name), $args['label_for']);
		$value = $options[$args['label_for']];
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-text-area.php';
	}

	/**
	 * Render a checkbox for an option.
	 * 
	 * @since    1.0.0
	 */
	public function checkbox($args)
	{
		$options = get_option($this->plugin_name);
		$id = esc_attr($args['label_for']);
		$name = sprintf('%s[%s]', esc_attr($this->plugin_name), $args['label_for']);
		include plugin_dir_path(__FILE__) . 'partials/azure-sso-admin-option-checkbox.php';
	}
}
