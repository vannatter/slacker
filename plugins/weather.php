<?php

	class weather extends Slacker {

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

			$data = $this->run_curl("http://api.wunderground.com/api/" . $this->config['wunderground_api_key']  . "/geolookup/conditions/q/IA/" . $this->command_text . ".json", "GET");
			$data_decoded = json_decode($data);
			
			if (!isset($data_decoded->response->error)) {
				$this->content = sprintf(
					"currently %s and %s [ feels like %s ] in %s (%s)",
					$data_decoded->{'current_observation'}->{'weather'},
					$data_decoded->{'current_observation'}->{'temperature_string'},
					$data_decoded->{'current_observation'}->{'feelslike_string'},
					$data_decoded->{'current_observation'}->{'display_location'}->{'full'},
					$this->command_text
				);
			} else {
				$this->content = $data_decoded->response->error->description;
			}
		}

	}

?>