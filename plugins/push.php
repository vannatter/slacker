<?php
	
	class push extends Slacker {

		protected $content;
		private $config;
		
		function __construct() {
			if (file_exists("config/".get_class($this).".php")) { 
				include("config/".get_class($this).".php");
				if (isset(${'config_'.get_class($this)})) {
					$this->config = ${'config_'.get_class($this)};
				}
			}
			parent::__construct();

			$this->content = "pushing " . $this->command_text . " to production .. ";
			if (isset($this->config['repos'][$this->command_text])) {
				$this->content .= $this->run_curl($this->config['repos'][$this->command_text], "POST");
				$this->content .= "\nrebuild completed.";
			} else {
				$this->content .= "repo not configured.";
			}
		}

	}

?>