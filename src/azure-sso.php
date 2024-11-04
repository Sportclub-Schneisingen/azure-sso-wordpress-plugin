<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Azure_SSO
 *
 * @wordpress-plugin
 * Plugin Name:       Azure SSO
 * Plugin URI:        http://example.com/azure-sso/
 * Description:       Allows users to log in to WordPress using Microsoft Entra ID SSO.
 * Version:           1.0.0
 * Author:            Janick Lehmann
 * Author URI:        https://janicklehmann.ch/
 * License:           MIT
 * License URI:       https://mit-license.org/
 * Text Domain:       azure-sso
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Define a logging function
if (!function_exists('write_log')) {
    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('AZURE_SSO_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-azure-sso-activator.php
 */
function activate_azure_sso()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-azure-sso-activator.php';
	Azure_SSO_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-azure-sso-deactivator.php
 */
function deactivate_azure_sso()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-azure-sso-deactivator.php';
	Azure_SSO_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_azure_sso');
register_deactivation_hook(__FILE__, 'deactivate_azure_sso');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-azure-sso.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_azure_sso()
{
	$plugin = new Azure_SSO();
	$plugin->run();
}

run_azure_sso();
