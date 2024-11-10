<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 * @author     Janick Lehmann <j.clehmann@hotmail.com>
 */
class Azure_SSO_Activator
{

	/**
	 * Runs when plugin is activated.
	 *
	 * Registers the rewrite endpoint for the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		add_rewrite_endpoint('azure-sso', EP_ROOT); // TODO: Get plugin name
		flush_rewrite_rules();
	}
}
