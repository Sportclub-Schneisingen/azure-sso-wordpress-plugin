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
    id="<?php echo $id; ?>"
    name="<?php echo $name; ?>"
    value="1"
    <?php checked($options[$args['label_for']]); ?> />