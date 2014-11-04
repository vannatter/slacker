<?php
	
	class spotify extends Slacker {

		protected $content;
		protected $webhook_settings;
		protected $config;
		
		function __construct() {
			if (file_exists("config/".get_class($this).".php")) { 
				include("config/".get_class($this).".php");
				if (isset(${'config_'.get_class($this)})) {
					$this->config = ${'config_'.get_class($this)};
				}
			}
			parent::__construct();

			$this->webhook_settings = array(
				"icon_url" => $this->webhook_setting("icon_url", "http://i.imgur.com/OJaGIQH.png"),
				"icon_emoji" => $this->webhook_setting("icon_emoji", ""),
				"username" => $this->webhook_setting("username", "spotify-bot")
			);
				
			$data = $this->run_curl("https://api.spotify.com/v1/search?q=" . urlencode($this->command_text) . "&type=artist,track&limit=1", "GET");
			$data_decoded = json_decode($data);

			if (count($data_decoded->tracks->items) > 0) {
				$this->content = $this->command_text . "' found " . $data_decoded->tracks->items[0]->name . " by " . $data_decoded->tracks->items[0]->artists[0]->name . " - https://embed.spotify.com/?uri=" . $data_decoded->tracks->items[0]->uri;
			} else {
				$this->content = "can't find anything on spotify for '" . $this->command_text . "'!";
			}
		}

	}

?>