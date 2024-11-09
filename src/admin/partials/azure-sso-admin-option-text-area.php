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

<textarea
    id="<?php esc_attr_e($args['id']); ?>"
    name="<?php esc_attr_e(sprintf('%s[%s]', $this->plugin_name, $args['id'])); ?>">
    <?php esc_attr_e($this->options[$args['id']]); ?>
</textarea>