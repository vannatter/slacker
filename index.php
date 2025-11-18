<?php

///////////////////////////////////////////////////////////////////
/// Slacker: a simple post-back bot for Slack
/// http://github.com/vannatter/slacker
///////////////////////////////////////////////////////////////////

declare(strict_types=1);

// Require Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load();
}

// Fallback to legacy config if .env doesn't exist
if (!file_exists(__DIR__ . '/.env') && file_exists(__DIR__ . '/config/slacker.php')) {
    require __DIR__ . '/config/slacker.php';
}

// Input validation
if (!isset($_REQUEST['text'], $_REQUEST['user_name'], $_REQUEST['token'])) {
    http_response_code(400);
    exit('Bad Request: Missing required parameters');
}

// Sanitize inputs
$text = trim($_REQUEST['text']);
$userName = htmlspecialchars($_REQUEST['user_name'], ENT_QUOTES, 'UTF-8');
$token = $_REQUEST['token'];

// Extract plugin name
$pluginName = trim(strtolower(strtok($text, ' ')));

// Whitelist of allowed plugins (security: prevent arbitrary class instantiation)
$allowedPlugins = [
    'help', 'weather', 'weatherext', 'spotify', 'say', 'push',
    'pug', 'corgi', 'listen', 'holiday', 'armory', 'starwars'
];

if (empty($pluginName)) {
    exit("@{$userName}, please specify a command.");
}

if (!in_array($pluginName, $allowedPlugins, true)) {
    exit("@{$userName}, unknown command.");
}

// Try to instantiate the plugin class
try {
    if (class_exists($pluginName)) {
        $slacker = new $pluginName();
        echo $slacker->output();
    } else {
        exit("@{$userName}, command not available.");
    }
} catch (Exception $e) {
    // Log error in production, show in debug mode
    if (defined('SLACKER_DEBUG') && SLACKER_DEBUG) {
        exit("Error: " . $e->getMessage());
    }
    exit("@{$userName}, an error occurred processing your command.");
}

?>