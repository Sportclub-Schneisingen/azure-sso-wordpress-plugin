<?php

/**
 * Provides the markup for a password field.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<input
    class="regular-text code ltr"
    type="password"
    id="<?php esc_attr_e($args['id']); ?>"
    name="<?php esc_attr_e(sprintf('%s[%s]', $this->plugin_name, $args['id'])); ?>"
    value="<?php esc_attr_e($this->options[$args['id']]); ?>" />