# Slacker

Simple post-back bot for Slack written in PHP.

### What is Slacker?

An extremely simple approach to a post-back bot for the amazing Slack service. 

### What do I need?

You'll need a slack team account, API access, and a bit of time. 

### What can the bot do?

Currently we've only got a few simple plugins, but we're adding things as we want them. 

### How can I help?

Write any plugins you want, we'd be happy to have help!

### How do I configure the bot?

You'll need to setup your config/ files, notably slacker.php:

	define('SLACKER_BOT_NAME', 'slacker'); // your bots default name (for webhooks)
	define('SLACKER_DEBUG', 0); // for turning on/off debugging

	define('SLACK_POSTBACK_TYPE', 2); // 1 = slackbot remote controls, 2 = webhook
	
	define('SLACK_HOSTNAME', ''); // example: vannatter.slack.com, you'd use vannatter
	define('SLACK_TOKEN', ''); // incoming token to make sure its your bot posting
	define('SLACK_API_KEY', ''); // api from your slack webhook
	define('SLACK_CHANNEL', 'general'); // channel to post to

### Settings on Slack (Setting up your bot as an Integration)

The first thing you'll want to do is setup your trigger integration - this is how slack knows to send information to your bot.

Go to Slack > Configure Integrations and add a "Slash Command"

You'll want to point this slash command (POST or GET, either should be handled) to wherever you've setup this bot.

You'll want to make sure you copy the "token" from your slash command into your config/slacker.php constant named SLACK_TOKEN.

So if you setup your slash command to be /bot, all commands triggered by /bot (ie. /bot weather 44685) will be sent to your Slacker bot install.

Next, you'll need to configure a post back integration. Slacker supports both Slackbot Remote Controls and Webhooks, but we really recommend using Webbooks (much more flexible / customizeable).

Go to Slack > Configure Integrations and add a "Incoming Webhook".

Copy your "Webhook URL" into your config/slacker.php constant named SLACK_WEBHOOK_URL and make sure you set the constant SLACK_POSTBACK_TYPE to 2.

Voila, that's really all you need to integrate!

### Plugin Configuration

We've included sample configs for each plugin in config-default/ but you'll want to move these to /config/ as you're configuring your bot.

Most of these settings should be self-explanatory:

	'help_command' -- this is the text displayed when a user runs /trigger help
	'webhook_settings' -- these are overrides for the default plugin options for different webhook settings like name, icon or emoji. 
	
	
