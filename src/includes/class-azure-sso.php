<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 * @author     Janick Lehmann <j.clehmann@hotmail.com>
 */
class Azure_SSO
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Azure_SSO_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current base file of the plugin.
	 * 
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_file    The current base file of the plugin.
	 */
	protected $plugin_file;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('AZURE_SSO_VERSION')) {
			$this->version = AZURE_SSO_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		if (defined('AZURE_SSO_PLUGIN_FILE')) {
			$this->plugin_file = AZURE_SSO_PLUGIN_FILE;
		} else {
			$this->plugin_file = plugin_dir_path(__FILE__) . 'azure-sso.php';
		}
		$this->plugin_name = 'azure-sso';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Azure_SSO_Loader. Orchestrates the hooks of the plugin.
	 * - Azure_SSO_i18n. Defines internationalization functionality.
	 * - Azure_SSO_Admin. Defines all hooks for the admin area.
	 * - Azure_SSO_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-azure-sso-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-azure-sso-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-azure-sso-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-azure-sso-public.php';

		$this->loader = new Azure_SSO_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Azure_SSO_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Azure_SSO_Admin($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
		$this->loader->add_filter('admin_init', $plugin_admin, 'display_notices');

		$this->loader->add_filter('plugin_action_links_' . $this->plugin_file, $plugin_admin, 'link_settings');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Azure_SSO_Public($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('init', $plugin_public, 'add_rewrite');
		$this->loader->add_action('init', $plugin_public, 'start_session');
		$this->loader->add_action('login_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('login_init', $plugin_public, 'auto_redirect_to_sso');
		$this->loader->add_action('login_form', $plugin_public, 'show_login_form');
		$this->loader->add_action('wp_logout', $plugin_public, 'end_session');
		$this->loader->add_action('wp_login', $plugin_public, 'end_session');

		$this->loader->add_filter('authenticate', $plugin_public, 'authenticate', 10, 3);
		$this->loader->add_filter('login_redirect', $plugin_public, 'redirect', 10, 3);
		$this->loader->add_filter('template_include', $plugin_public, 'start_sso', 10, 1);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Azure_SSO_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
