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
	}

	/**
	 * Adds options page link to the admin menu (submenu of the settings menu).
	 * 
	 * @since    1.0.0
	 */
	public function add_menu()
	{
		add_options_page(
			'Azure SSO Settings',
			'Azure SSO',
			'manage_options',
			$this->plugin_name,
			array($this, 'options_page'),
		);
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
	 * Register settings options.
	 * 
	 * @since    1.0.0
	 */
	public function register_settings()
	{
		$settings = [
			'client_id'     => [
				'id'          => $this->plugin_name . '-option-client-id',
				'type'        => 'string',
				'description' => 'Application (Client) ID',
			],
			'client_secret' => [
				'id'          => $this->plugin_name . '-option-client-secret',
				'type'        => 'string',
				'description' => 'Client Secret',
			],
			'tenant_id'     => [
				'id'          => $this->plugin_name . '-option-tenant-id',
				'type'        => 'string',
				'description' => 'Tenant ID',
			],
			'redirect_uri'  => [
				'id'          => $this->plugin_name . '-option-redirect-uri',
				'type'        => 'string',
				'description' => 'Redirect URI',
			],
			'button_text'   => [
				'id'          => $this->plugin_name . '-option-button-text',
				'type'        => 'string',
				'description' => 'Login Button Text',
				'default'     => 'Login with Azure AD',
			],
			'auto_redirect' => [
				'id'          => $this->plugin_name . '-option-auto-redirect',
				'type'        => 'boolean',
				'description' => 'Auto Redirect to SSO Login Page',
			],
		];

		foreach ($settings as $setting) {
			register_setting(
				$this->plugin_name . '-options',
				$setting['id'],
				[
					'type' => $setting['type'],
					'description' => $setting['description'],
					'default' => $setting['default'] ?? null,
				]
			);
		}
	}

	/**
	 * Register settings sections.
	 * 
	 * @since    1.0.0
	 */
	public function register_sections()
	{
		$sections = array(
			'general'  => [
				'id'       => $this->plugin_name . '-section-general',
				'title'    => 'General Options',
				'callback' => function () {
					echo 'Configure client ID, client secret, tenant ID and redirect URI.
					      Get them from your Azure AD app registration.';
				},
			],
			'login'    => [
				'id'       => $this->plugin_name . '-section-login',
				'title'    => 'Login Page Options',
				'callback' => function () {
					echo 'Configure the login page behavior or style the login page button.';
				},
			],
			'advanced' => [
				'id'       => $this->plugin_name . '-section-advanced',
				'title'    => 'Azure SSO Options',
				'callback' => function () {
					echo 'Configure advanced options for SSO here.';
				},
			],
		);

		foreach ($sections as $section) {
			add_settings_section(
				$section['id'],
				$section['title'],
				$section['callback'],
				$this->plugin_name
			);
		}
	}

	/**
	 * Register settings fields.
	 *
	 * @since    1.0.0
	 */
	public function register_fields()
	{
		$fields = [
			'client_id'     => [
				'id'       => $this->plugin_name . '-option-client-id',
				'title'    => 'Client ID',
				'callback' => array($this, 'text_field'),
				'section'  => $this->plugin_name . '-section-general',
				'args'     => [
					'id'    => $this->plugin_name . '-option-client-id',
					'class' => 'regular-text',
				],
			],
			'client_secret' => [
				'id'       => $this->plugin_name . '-option-client-secret',
				'title'    => 'Client Secret',
				'callback' => array($this, 'text_field'),
				'section'  => $this->plugin_name . '-section-general',
				'args'     => [
					'id'    => $this->plugin_name . '-option-client-secret',
					'class' => 'regular-text',
				],
			],
			'tenant_id'     => [
				'id'       => $this->plugin_name . '-option-tenant-id',
				'title'    => 'Tenant ID',
				'callback' => array($this, 'text_field'),
				'section'  => $this->plugin_name . '-section-general',
				'args'     => [
					'id'    => $this->plugin_name . '-option-tenant-id',
					'class' => 'regular-text',
				],
			],
			'redirect_uri'  => [
				'id'       => $this->plugin_name . '-option-redirect-uri',
				'title'    => 'Redirect URI',
				'callback' => array($this, 'text_field'),
				'section'  => $this->plugin_name . '-section-general',
				'args'     => [
					'id'    => $this->plugin_name . '-option-redirect-uri',
					'class' => 'regular-text',
				],
			],
			'button_text'   => [
				'id'       => $this->plugin_name . '-option-button-text',
				'title'    => 'Login Button Text',
				'callback' => array($this, 'text_field'),
				'section'  => $this->plugin_name . '-section-login',
				'args'     => [
					'id'    => $this->plugin_name . '-option-button-text',
					'class' => 'regular-text',
				],
			],
			'auto_redirect' => [
				'id'       => $this->plugin_name . '-option-auto-redirect',
				'title'    => 'Auto Redirect to SSO Login Page',
				'callback' => array($this, 'checkbox'),
				'section'  => $this->plugin_name . '-section-advanced',
				'args'     => [
					'id'    => $this->plugin_name . '-option-auto-redirect',
				],
			],
		];

		foreach ($fields as $field) {
			add_settings_field(
				$field['id'],
				$field['title'],
				$field['callback'],
				$this->plugin_name,
				$field['section'],
				$field['args']
			);
		}
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/azure-sso-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/azure-sso-admin.js', array('jquery'), $this->version, false);
	}
}
