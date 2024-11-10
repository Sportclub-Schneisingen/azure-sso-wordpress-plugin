<?php

/**
 * Provides the markup for a text field.
 *
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin/partials
 */
?>

<input
    class="regular-text"
    type="text"
    id="<?php esc_attr_e($args['id']); ?>"
    name="<?php esc_attr_e(sprintf('%s[%s]', $this->plugin_name, $args['id'])); ?>"
    value="<?php esc_attr_e($this->options[$args['id']]); ?>" />