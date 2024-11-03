<?php

/**
 * Provides the markup for a text field.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<input type="text" name="<?php echo $args['id']; ?>" value="<?php echo get_option($args['id']); ?>" />