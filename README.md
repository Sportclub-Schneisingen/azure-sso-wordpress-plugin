# Azure SSO
Azure SSO is a small and simple WordPress plugin that allows you to sign in to your WordPress site using Microsoft Entra ID as identity provider.

## Introduction
This plugin allows users to sign in to WordPress using Microsoft Entra ID as identity provider.
Organizations which use Microsoft 365 can use this plugin to allow their users to sign in to WordPress using their Microsoft Entra ID account.

**This project is not affiliated nor endorsed by Microsoft.**

### Features
- [X] Sign in existing WordPress users when email address matches.
- [X] Automatically redirect to SSO login from the WordPress login page.
- [X] Fallback to WordPress login.
- [ ] Support POST requests to IdP.
- [X] Option for user creation on first sign in.
- [ ] Option for user role/user group mappings.
- [ ] Support PKCE.
- [ ] ID token validation.
- [ ] Configure plugin using environment variables.
- [ ] Automatic release pipeline for GitHub releases.

### Inspired By
It was inspired by [AAD SSO Wordpress](https://github.com/psignoret/aad-sso-wordpress) and [WP SSO for Azure AD](https://gitlab.com/qlcvea/wp-sso-for-azure-ad).

## Getting Started
1. Run `docker compose up -d` to start the WordPress containers.
1. Go to `http://localhost:8080/wp-admin/` and log in with username `admin` and password `admin`.
2. Go to Plugins > Installed Plugins.
3. Activate the **Azure SSO** plugin.

### Internationalization
The .pot file can be created using the WP-CLI.
Use the following command to create the .pot file:
```bash
# Install WP-CLI if not already done
# from the root directory of the repository
wp i18n make-pot ./src ./src/languages/azure-sso.pot --headers='{"Report-Msgid-Bugs-To":"https://github.com/Sportclub-Schneisingen/azure-sso-wordpress-plugin"}' --ignore-domain
```

After the file was created, append `"X-Domain: azure-sso"` to the headers of the .pot file.
The domain cannot be specified because the plugin uses the `plugin_name` variable as its text domain, which is not loaded during string extraction.

## Installation
1. Download the latest release as ZIP file.
1. Go to the WordPress admin panel.
1. Go to Plugins > Add New.
1. Click on the "Upload Plugin" button.
1. Select the ZIP file and click on the "Install Now" button.
1. Activate the plugin.

## Configuration
1. Go to the [Azure portal](https://portal.azure.com/) and create a new app registration.
1. Configure the Web platform with the correct redirect URI.
1. Add the groups claim to the app registration in "Token configuration" (required for role mapping).
1. Copy the application (client) ID and the tenant ID.
1. Create a new client secret and copy its value.
1. Go to the plugin settings and enter the client ID, client secret and tenant ID.
1. Configure role mappings in plugin settings if needed.
1. Save the settings.

## License
This project is licensed under the GNU GPLv3 - see the [LICENSE](LICENSE) file for details.

This project is based on the [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate),
which is licensed under the GNU GPLv2 or later.

## Contributing
Contributions are welcome! Please feel free to submit a pull request.

## Support
*This project is a work in progress. The plugin is provided as-is, without any guarantees.*

In case of any bugs or security concerns, please submit an issue.
If you can fix the issue yourself, please submit a pull request.
