<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/admin/partials
 */
?>

<div class="wrap">
    <h2><?php esc_html_e(get_admin_page_title()) ?></h2>
    <form method="post" action="options.php">
        <?php
        settings_fields($this->plugin_name);
        do_settings_sections($this->plugin_name);
        submit_button();
        ?>
    </form>
</div>