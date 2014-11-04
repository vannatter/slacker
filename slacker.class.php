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
			$this->channel_name = $_REQUEST['channel_name'];
			$this->user_name = $_REQUEST['user_name'];
			$this->token = $_REQUEST['token'];
			$this->full_command = $_REQUEST['text'];
			$this->command = trim(strtolower(strtok($this->full_command, ' ')));
			$this->command_text = trim(str_replace($this->command, "", $this->full_command));

			if ( ($this->token != SLACK_TOKEN) || (!$this->full_command) ) { 
				$this->mute_error('unauthorized');
			}
			$this->webhook_settings = array(
				"username" => SLACKER_BOT_NAME
			);
		}
		
		function output() {
			$out = $this->pref_prefix() . $this->content;
			if (defined('SLACK_POSTBACK_TYPE')) {
				if (SLACK_POSTBACK_TYPE == 1) {
					if (SLACKER_DEBUG) {
						return $out;
					} else {
						$this->run_curl("https://" . SLACK_HOSTNAME . ".slack.com/services/hooks/slackbot?token=" . SLACK_API_KEY . "&channel=%23" . SLACK_CHANNEL, "POST", $out);
						return "";
					}
				} else {
					if (defined('SLACK_WEBHOOK_URL')) {
						$output_arr = array("text" => $out);
						if (!empty($this->webhook_settings)) {
							$output_arr = array_merge($output_arr, $this->webhook_settings);
						}
						if (SLACKER_DEBUG) {
							return json_encode($output_arr);
						} else {
							$this->run_curl(SLACK_WEBHOOK_URL, "POST", json_encode($output_arr));
							return "";
						}
					} else {
						return "";
					}
				}
			} else {
				return $out;
			}
		}

		protected function run_curl($curl_url, $request_type="GET", $post_fields="") {
			$ch = curl_init($curl_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_type);
			if ($post_fields) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
			}
			$r = curl_exec($ch);
			return $r;
		}
		
		protected function pref_prefix() {
			$prefix = (RESPONSE_PREPEND_USER) ? "@".$this->user_name.", " : "";
			return $prefix;
		}

		protected function mute_error($output="") {
			echo (SLACKER_DEBUG) ? $output : "";
			exit;
		}
		
		protected function webhook_setting($config_var, $default_val) {
			if (!empty($this->config['webhook_settings'][$config_var])) {
				return $this->config['webhook_settings'][$config_var];
			} else {
				return $default_val;
			}
		}

	}

?>