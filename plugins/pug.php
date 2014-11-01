<?php
	
	class pug extends Slacker {

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