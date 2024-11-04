<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Azure_SSO
 * @subpackage Azure_SSO/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="azure-sso-wrap">
    <a id="azure-sso-login-button" class="button button-large" href="<?php echo esc_html($login_url) ?>" >
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="currentColor" viewBox="0 0 448 512">
            <path d="M0 32h214.6v214.6H0V32zm233.4 0H448v214.6H233.4V32zM0 265.4h214.6V480H0V265.4zm233.4 0H448V480H233.4V265.4z"/>
        </svg>
        <?php echo esc_html($button_text); ?>
    </a>
</div>