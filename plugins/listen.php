<?php

	class listen extends Slacker {

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

			$data = $this->run_curl("http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=" . urlencode($this->command_text) . "&api_key=" . $this->config['lastfm_api_key'] . "&format=json", "GET");
			$data_decoded = json_decode($data);
			
			if (!isset($data_decoded->error)) {
				if (isset($data_decoded->recenttracks->track[0]->{'@attr'}->nowplaying)) {
					$listen_verb = "is listening to";
				} else {
					$listen_verb = "last listened to";
				}
				$this->content = $this->command_text . " " . $listen_verb . " " . $data_decoded->recenttracks->track[0]->name . " by " . $data_decoded->recenttracks->track[0]->artist->{'#text'} . ". " . $data_decoded->recenttracks->track[0]->url;
			} else {
				$this->content = $data_decoded->message;
			}
		}
		
	}

?>