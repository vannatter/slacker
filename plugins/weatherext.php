<?php

	class weatherext extends Slacker {

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

			$data = $this->run_curl("http://api.wunderground.com/api/" . $this->config['wunderground_api_key']  . "/geolookup/conditions/forecast/q/IA/" . $this->command_text . ".json", "GET");
			$data_decoded = json_decode($data);
			
			if (!isset($data_decoded->response->error)) {
				$this->content = "getting extended weather for " . $data_decoded->{'current_observation'}->{'display_location'}->{'full'} . "... \n";
				$forecast = $data_decoded->{'forecast'}->{'simpleforecast'};
				foreach ($forecast->{'forecastday'} as $day) {
					$this->content .= sprintf(
						"%s %s F / %s F %s\n",
						$day->{'date'}->{'weekday'},
						$day->{'high'}->{'fahrenheit'},
						$day->{'low'}->{'fahrenheit'},
						$day->{'conditions'}
					);
				}
			} else {
				$this->content = $data_decoded->response->error->description;
			}
		}

	}

?>