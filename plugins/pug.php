<?php
	
	class pug extends Slacker {

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

			$this->webhook_setting("icon_url", "");
			$this->webhook_setting("icon_emoji", ":dog:");
			$this->webhook_setting("username", "pug-bot");
			
			$data = $this->run_curl("http://pugme.herokuapp.com/random", "GET");
			$data_decoded = json_decode($data);

			if (isset($data_decoded->pug)) {
				$this->content = "here's a random pug! " . $data_decoded->pug;
			} else {
				$this->mute_error('pug not found');
			}
		}
		
	}

?>