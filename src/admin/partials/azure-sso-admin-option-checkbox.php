<?php

/**
 * Provides the markup for a checkbox.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<input type="checkbox" name="<?php echo $args['id']; ?>" value="1" <?php checked(1, get_option($args['id']), true); ?> />