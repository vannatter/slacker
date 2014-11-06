<?php

	class say extends Slacker {
		
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
		
			$this->webhook_setting("username", "");
			$this->webhook_setting("icon_url", "");
			$this->webhook_setting("icon_emoji", ":information_source:");
			
			$this->content = $this->command_text;
		}

	}
	
?>