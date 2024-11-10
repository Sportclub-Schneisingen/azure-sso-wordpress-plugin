<?php

/**
 * Provides the markup for a checkbox.
 *
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin/partials
 */
?>

<input
    type="checkbox"
    id="<?php esc_attr_e($args['id']); ?>"
    name="<?php esc_attr_e(sprintf('%s[%s]', $this->plugin_name, $args['id'])); ?>"
    value="1"
    <?php checked($this->options[$args['id']]); ?> />