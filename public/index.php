<?php

declare(strict_types=1);

/*
 * Slacker: a simple slash-command bot for Slack
 * https://github.com/vannatter/slacker
 *
 * Point your web server's document root at this directory.
 */

use Slacker\App;
use Slacker\Config;
use Slacker\HttpResponse;

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    $app = App::create(Config::load(dirname(__DIR__)));

    $response = $app->handle(
        method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
        headers: [
            'X-Slack-Request-Timestamp' => $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'] ?? '',
            'X-Slack-Signature' => $_SERVER['HTTP_X_SLACK_SIGNATURE'] ?? '',
        ],
        body: (string) file_get_contents('php://input'),
    );
} catch (\Throwable $e) {
    // Never leak configuration details or stack traces to the caller.
    error_log('[slacker] fatal: ' . $e->getMessage());
    $response = HttpResponse::json(['error' => 'server error'], 500);
}

http_response_code($response->status);
header('Content-Type: application/json; charset=utf-8');
echo $response->body;
