<?php

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Azure_SSO
 * @subpackage Azure_SSO/includes
 * @author     Janick Lehmann <j.clehmann@hotmail.com>
 */
class Azure_SSO_Deactivator
{

	/**
	 * Runs when plugin is deactivated.
	 *
	 * Unregisters the rewrite endpoint for the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		flush_rewrite_rules();
	}
}
