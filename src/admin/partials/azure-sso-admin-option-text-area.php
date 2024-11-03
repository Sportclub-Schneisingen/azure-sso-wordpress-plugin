<?php

/**
 * Provides the markup for a text area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<textarea name="<?php echo $args['id']; ?>">
    <?php echo get_option($args['id']); ?>
</textarea>