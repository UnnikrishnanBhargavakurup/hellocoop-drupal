# Hellocoop Drupal Module

This module integrates the [Hellō Identity Provider](https://www.hello.coop/) into Drupal, enabling seamless user authentication using Hellō’s cloud identity wallet. It leverages the Hellocoop PHP library to provide support for social logins, email, and phone-based authentication while prioritizing user privacy.

## Features
- **Social Login**: Supports popular providers like Google, Facebook, etc.
- **Email and Phone Login**: Allows users to authenticate with verified email addresses or phone numbers.
- **Privacy-Focused**: Empowers users to control their digital identities without tracking.
- **Easy Configuration**: Provides a simple interface to configure API credentials and other settings.

## Requirements
- Drupal 9.x or 10.x
- PHP 7.4 or higher
- [Hellocoop PHP Library](https://github.com/UnnikrishnanBhargavakurup/hellocoop)

## Installation

### Step 1: Install the Module
1. Download and enable the module:
   ```bash
   composer require unnikrishnanbhargavakurup/hellocoop-drupal
   drush en hellocoop
   ```

2. Clear the Drupal cache:
   ```bash
   drush cr
   ```

### Step 2: Configure the Module
1. Navigate to `Configuration > People > Hellocoop Settings` in your Drupal admin interface.
2. Fill in the following fields:
   - **API Route**: Base API route (e.g., `/api/hellocoop`).
   - **Application ID**: Obtain this from the [Hellō Console](https://console.hello.coop/).
   - **Secret Key**: Generate a 32-byte hex key using `openssl rand -hex 32`.

3. Save the configuration.

### Step 3: Enable Routing
Ensure your site’s routing system correctly maps the Hellocoop API endpoint:
- For example, if the API route is `/api/hellocoop`, ensure it is accessible and routed to the Hellocoop handler.

## Usage

Once configured, the module will:
1. Handle user authentication requests via Hellō.
2. Manage user sessions, including login and logout operations.
3. Provide endpoints for the Hellocoop API, ensuring smooth integration with the Hellō service.

## Customization
This module is built on top of the Hellocoop PHP library. You can extend or override functionality using Drupal’s plugin and service architecture to suit your application’s needs.

## Code Quality
The module follows coding standards enforced by a pre-commit hook for linting. To set up the pre-commit hook in your local development environment:

1. **Copy the pre-commit hook to your Git hooks directory**:
   ```bash
   cp pre-commit .git/hooks/
   ```

2. **Make the hook executable**:
   ```bash
   chmod +x .git/hooks/pre-commit
   ```

This ensures consistent code quality and adherence to coding standards.

## Contributing
Contributions are welcome! Please create an issue or submit a pull request. Ensure your code adheres to Drupal coding standards and includes relevant tests.

## License
This module is licensed under the MIT License. See the [LICENSE](../LICENSE) file for details.

---
For more information on Hellō, visit the [official website](https://www.hello.coop/).

