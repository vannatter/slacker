<?php

	class help extends Slacker {
		
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

			$this->content = "commands available: ";			
			$config_files = array_diff(scandir(SLACKER_CONFIG_DIR), array('..', '.', 'slacker.php'));;
			foreach ($config_files as $config_file) {
				include(SLACKER_CONFIG_DIR."/".$config_file);
				$this->content .= " " . ${'config_'.trim(str_replace(".php","",$config_file))}['help_command'] . ",";
			}
			$this->content = trim(rtrim($this->content, ','));
		}
		
	}
	
?>