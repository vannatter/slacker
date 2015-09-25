<?php
	
	class starwars extends Slacker {

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
			$this->webhook_setting("icon_emoji", ":clock4:");
			$this->webhook_setting("username", "starwars-bot");

			$release_date = strtotime("December 18, 2015 12:01 AM");
			$remain = $release_date - time();
			$days_remaining = floor($remain / 86400);
			$hours_remaining = floor(($remain % 86400) / 3600);

			$this->content = "There are " . $days_remaining . " days and " . $hours_remaining . " hours remaining until Star Wars: Episode VI release!";
		}

	}

?>