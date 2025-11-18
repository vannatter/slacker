<?php

class Slacker {

	public $full_command;
	public $command;
	public $command_text;
	public $user_name;
	public $channel_name;
	protected $webhook_settings;
	protected $token;

	function __construct() {
		$this->channel_name = $_REQUEST['channel_name'] ?? '';
		$this->user_name = $_REQUEST['user_name'] ?? '';
		$this->token = $_REQUEST['token'] ?? '';
		$this->full_command = $_REQUEST['text'] ?? '';
		$this->command = trim(strtolower(strtok($this->full_command, ' ')));
		$this->command_text = trim(str_replace($this->command, "", $this->full_command));

		// Get token from environment or constant
		$expectedToken = $_ENV['SLACK_TOKEN'] ?? (defined('SLACK_TOKEN') ? SLACK_TOKEN : '');

		if (($this->token != $expectedToken) || (!$this->full_command)) {
			$this->mute_error('unauthorized');
		}

		$botName = $_ENV['SLACKER_BOT_NAME'] ?? (defined('SLACKER_BOT_NAME') ? SLACKER_BOT_NAME : 'slacker');

		$this->webhook_settings = array(
			"username" => $botName,
			"link_names" => 1,
			"unfurl_links" => true
		);
	}

	function output() {
		$out = $this->pref_prefix() . $this->content;

		$postbackType = $_ENV['SLACK_POSTBACK_TYPE'] ?? (defined('SLACK_POSTBACK_TYPE') ? SLACK_POSTBACK_TYPE : null);

		if ($postbackType) {
			if ($postbackType == 1 || $postbackType === 'slackbot') {
				return $this->outputSlackbot($out);
			} else {
				return $this->outputWebhook($out);
			}
		} else {
			return $out;
		}
	}

	private function outputSlackbot($out) {
		$debug = $_ENV['SLACKER_DEBUG'] ?? (defined('SLACKER_DEBUG') ? SLACKER_DEBUG : false);
		$hostname = $_ENV['SLACK_HOSTNAME'] ?? (defined('SLACK_HOSTNAME') ? SLACK_HOSTNAME : '');
		$apiKey = $_ENV['SLACK_API_KEY'] ?? (defined('SLACK_API_KEY') ? SLACK_API_KEY : '');
		$channel = $_ENV['SLACK_CHANNEL'] ?? (defined('SLACK_CHANNEL') ? SLACK_CHANNEL : 'general');

		if ($debug) {
			return $out;
		} else {
			$url = "https://" . $hostname . ".slack.com/services/hooks/slackbot?token=" . $apiKey . "&channel=%23" . $channel;
			$this->run_curl($url, "POST", $out);
			return "";
		}
	}

	private function outputWebhook($out) {
		$webhookUrl = $_ENV['SLACK_WEBHOOK_URL'] ?? (defined('SLACK_WEBHOOK_URL') ? SLACK_WEBHOOK_URL : null);
		$debug = $_ENV['SLACKER_DEBUG'] ?? (defined('SLACKER_DEBUG') ? SLACKER_DEBUG : false);

		if (!$webhookUrl) {
			return "";
		}

		$output_arr = array("text" => $out);
		if (!empty($this->webhook_settings)) {
			$output_arr = array_merge($output_arr, $this->webhook_settings);
		}

		if ($debug) {
			return json_encode($output_arr);
		} else {
			$this->run_curl($webhookUrl, "POST", json_encode($output_arr));
			return "";
		}
	}

	protected function run_curl($curl_url, $request_type="GET", $post_fields="") {
		$ch = curl_init($curl_url);

		// Basic options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_type);

		// Security: Enable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		// Timeout settings (prevent hanging)
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		// Follow redirects (max 3)
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

		// Set User-Agent
		curl_setopt($ch, CURLOPT_USERAGENT, 'Slacker-Bot/2.0');

		if ($post_fields) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
			// Set content type for JSON
			if ($this->isJson($post_fields)) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					'Content-Type: application/json',
					'Content-Length: ' . strlen($post_fields)
				]);
			}
		}

		$result = curl_exec($ch);

		// Error handling
		if ($result === false) {
			$error = curl_error($ch);
			$errno = curl_errno($ch);
			curl_close($ch);

			$debug = $_ENV['SLACKER_DEBUG'] ?? (defined('SLACKER_DEBUG') ? SLACKER_DEBUG : false);
			if ($debug) {
				error_log("CURL Error [{$errno}]: {$error}");
			}
			return false;
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// Check for HTTP errors
		if ($httpCode >= 400) {
			$debug = $_ENV['SLACKER_DEBUG'] ?? (defined('SLACKER_DEBUG') ? SLACKER_DEBUG : false);
			if ($debug) {
				error_log("HTTP Error: {$httpCode} for URL: {$curl_url}");
			}
			return false;
		}

		return $result;
	}

	private function isJson($string) {
		if (!is_string($string)) {
			return false;
		}
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}

	protected function pref_prefix() {
		$prependUser = $_ENV['RESPONSE_PREPEND_USER'] ?? (defined('RESPONSE_PREPEND_USER') ? RESPONSE_PREPEND_USER : true);
		$prefix = $prependUser ? "@".$this->user_name.", " : "";
		return $prefix;
	}

	protected function mute_error($output="") {
		$debug = $_ENV['SLACKER_DEBUG'] ?? (defined('SLACKER_DEBUG') ? SLACKER_DEBUG : false);
		echo $debug ? $output : "";
		exit;
	}

	protected function webhook_setting($config_var, $default_val) {
		if (!empty($this->config['webhook_settings'][$config_var])) {
			$this->webhook_settings[$config_var] = $this->config['webhook_settings'][$config_var];
		} else {
			if ($default_val) {
				$this->webhook_settings[$config_var] = $default_val;
			} else {
				unset($this->webhook_settings[$config_var]);
			}
		}
	}

}

?>
