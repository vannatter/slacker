<?php
	
	class corgi extends Slacker {

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

			$data = $this->run_curl("http://corginator.herokuapp.com/random", "GET");
			$data_decoded = json_decode($data);

			if (isset($data_decoded->corgi)) {
				$this->content = "here's a random corgi! " . $data_decoded->corgi;
			} else {
				$this->mute_error('corgi not found');
			}
		}

	}

?>