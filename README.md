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

	define('SLACK_HOSTNAME', ''); // example: vannatter.slack.com, you'd use vannatter
	define('SLACK_TOKEN', ''); // incoming token to make sure its your bot posting
	define('SLACK_API_KEY', ''); // api from your slack webhook
	define('SLACK_CHANNEL', 'general'); // channel to post to
