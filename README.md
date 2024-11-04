# Azure SSO
Azure SSO is a small and simple WordPress plugin that allows you to sign in to your WordPress site using Microsoft Entra ID as identity provider.

## Introduction
This plugin allows users to sign in to WordPress using Microsoft Entra ID as identity provider.
It is a collection of features of other plugins.

## Getting Started
1. Run `docker compose up -d` to start the WordPress containers.
1. Go to `http://localhost:8080/wp-admin/` and log in with username `admin` and password `admin`.
2. Go to Plugins > Installed Plugins.
3. Activate the **Azure SSO** plugin.

## Installation
1. Download the latest release as ZIP file.
1. Go to the WordPress admin panel.
1. Go to Plugins > Add New.
1. Click on the "Upload Plugin" button.
1. Select the ZIP file and click on the "Install Now" button.
1. Activate the plugin.

## Configuration
1. Go to the Azure portal and create a new app registration.
1. Add the redirect URI to the app registration.
1. Copy the application (client) ID.
1. Create a new client secret and copy its value.
1. Go to the plugin settings and enter the client ID and client secret.
1. Save the settings.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing
Contributions are welcome! Please feel free to submit a pull request.

## Support
I cannot provide any support for this plugin. Use it at your own risk.

In case of any bugs, please submit an issue.
If you can fix the issue yourself, please submit a pull request.
