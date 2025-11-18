# Slacker

Simple post-back bot for Slack written in PHP.

## What is Slacker?

An extremely simple approach to a post-back bot for the amazing Slack service.

## Requirements

- PHP 8.0 or higher
- Composer for dependency management
- PHP extensions: `curl`, `json`
- A Slack workspace with API access

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/vannatter/slacker.git
cd slacker
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure your bot

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` with your Slack credentials:

```env
SLACKER_BOT_NAME=slacker
SLACKER_DEBUG=false
RESPONSE_PREPEND_USER=true

SLACK_HOSTNAME=your-workspace
SLACK_TOKEN=your-slack-token-here
SLACK_CHANNEL=general

SLACK_POSTBACK_TYPE=webhook
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

### 4. Configure plugins (optional)

Plugin configurations are in `config-default/`. Copy any plugin config you want to use to the `config/` directory and customize:

```bash
cp config-default/weather.php config/
```

## Security Improvements (v2.0)

This version includes major security and modernization improvements:

### Security
- ✅ **Input Validation**: All user inputs are validated and sanitized
- ✅ **Plugin Whitelist**: Prevents arbitrary class instantiation attacks
- ✅ **SSL Verification**: All HTTPS connections verify SSL certificates
- ✅ **Secure Headers**: Proper Content-Type headers for API requests
- ✅ **Environment Variables**: Sensitive data stored in `.env` file (not in git)

### Modernization
- ✅ **PHP 8.0+ Compatible**: Removed deprecated `__autoload()` function
- ✅ **Composer Support**: Modern dependency management with PSR-4 autoloading
- ✅ **HTTPS Only**: All API calls use HTTPS
- ✅ **Error Handling**: Comprehensive error handling and logging
- ✅ **Timeout Protection**: HTTP requests have timeouts to prevent hanging

## What can the bot do?

Currently available plugins:

- **help** - List all available commands
- **weather** - Get current weather for a location
- **weatherext** - Get extended weather forecast
- **spotify** - Search for tracks on Spotify
- **listen** - Show what someone is listening to (Last.fm)
- **say** - Make the bot say something
- **push** - Send push notifications
- **pug** - Random pug pictures
- **corgi** - Random corgi pictures
- **holiday** - Check upcoming holidays
- **armory** - World of Warcraft armory lookup
- **starwars** - Star Wars countdown

## Slack Integration Setup

### Setting up Slash Commands

1. Go to your Slack workspace settings
2. Navigate to **Configure Integrations** → **Slash Commands**
3. Add a new slash command (e.g., `/bot`)
4. Point it to your Slacker installation URL
5. Copy the **token** to your `.env` file as `SLACK_TOKEN`

### Setting up Incoming Webhooks (Recommended)

1. Go to **Configure Integrations** → **Incoming Webhooks**
2. Add a new webhook
3. Copy the **Webhook URL** to your `.env` file as `SLACK_WEBHOOK_URL`
4. Set `SLACK_POSTBACK_TYPE=webhook` in your `.env`

## Plugin Configuration

Each plugin can have its own configuration file in the `config/` directory. Configuration options include:

- `help_command` - Text displayed when a user runs `/bot help`
- `webhook_settings` - Override default webhook settings (name, icon, emoji)
- Plugin-specific API keys and settings

Example plugin config (`config/weather.php`):

```php
<?php

$config_weather = array(
    'help_command' => '/bot weather [zipcode]',
    'wunderground_api_key' => 'your-api-key-here',
    'webhook_settings' => array(
        'username' => 'weather-bot',
        'icon_emoji' => ':cloud:'
    )
);
```

## Development

### Running in Debug Mode

Set `SLACKER_DEBUG=true` in your `.env` file to enable debug output.

### Creating New Plugins

1. Create a new PHP file in the `plugins/` directory
2. Extend the `Slacker` class
3. Add your plugin name to the whitelist in `index.php`
4. Create a configuration file in `config-default/`

Example plugin:

```php
<?php

class example extends Slacker {
    protected $content;
    protected $config;

    function __construct() {
        if (file_exists("config/".get_class($this).".php")) {
            include("config/".get_class($this).".php");
            if (isset(${'config_'.get_class($this)})) {
                $this->config = ${'config_'.get_class($this)};
            }
        }
        parent::__construct();

        // Your plugin logic here
        $this->content = "Hello from example plugin!";
    }
}
```

## Migration from v1.x

If you're upgrading from the original version:

1. **Install Composer** if you haven't already
2. Run `composer install` to install dependencies
3. Copy `config/slacker.php` settings to `.env` (see `.env.example`)
4. Update any custom plugins to handle potential `false` returns from `run_curl()`
5. Add any custom plugins to the whitelist in `index.php` (line 39)

## How can I help?

Write plugins! We'd be happy to accept pull requests with new functionality.

Please ensure:
- Code follows existing patterns
- API calls use HTTPS
- Proper error handling is included
- Plugin is added to the whitelist in `index.php`

## Need help?

- Report issues: https://github.com/vannatter/slacker/issues
- Contact: [@dustinvannatter](http://twitter.com/dustinvannatter)
- Website: [dustin.io](http://dustin.io/)

## License

MIT License - feel free to use and modify as needed!
