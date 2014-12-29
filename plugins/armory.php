<?php

	class armory extends Slacker {

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
			
			$armory_path = explode(" ", trim($this->command_text));

			if ( (!isset($armory_path[0])) || (!isset($armory_path[1])) || (!isset($armory_path[2])) ) {
				$this->content = "invalid armory character - try again.";
			} else {
				$url = $this->get_battlenet_prefix($armory_path[0]) . "/wow/character/" . urlencode($armory_path[1]) . "/" . urlencode($armory_path[2]) . "?fields=guild,stats,talents,items,reputation,achievements,professions,titles,pvp,mounts,companions,pets&locale=en_US&apikey=" . $this->config['armory_api_key'];
				$data = $this->run_curl($url, "GET");
				$data_decoded = json_decode($data);
				$armory_url = " :: http://armorylite.com/" . $armory_path[0] . "/" . $armory_path[1] . "/" . $armory_path[2];
				
				if (!isset($armory_path[3])) {
					$this->content = $this->format_default($data_decoded) . $armory_url;
				} else {
					switch ($armory_path[3]) {
						case "hk":
							$this->content = $this->format_hk($data_decoded) . $armory_url;
							break;
					}
				}
				
			}

		}

		private function format_default($d) {
			$out = $d->name . " of " . $d->realm . ", " . $d->level . " " . $this->get_character_gender($d->gender) . " " . $this->get_character_race($d->race) . " " . $this->get_character_class($d->class) . " (" . $this->get_character_faction($d->race) . ") - [ilvl:" . $d->items->averageItemLevel . "]";
			return $out;	
		}	

		private function format_hk($d) {
			$out = $d->name . " of " . $d->realm . " - " . $d->totalHonorableKills . " total honorable kills";
			return $out;	
		}	
		
		// helper functions
			
		private function get_battlenet_prefix($region) {
			$url = "";
			switch ($region) {
				case "us":
					$url = "https://us.api.battle.net";
					break;
				case "eu":
					$url = "https://eu.api.battle.net";
					break;
				case "kr":
					$url = "https://kr.api.battle.net";
					break;
				case "tw":
					$url = "https://tw.api.battle.net";
					break;
				case "sea":
					$url = "https://sea.api.battle.net";
					break;
				case "cn":
					$url = "https://battlenet.com.cn";
					break;
				default:
					$url = "https://us.api.battle.net";
					break;
			}
			return $url;
		}
		
		private function get_character_class($class) {
			$s = "";
			switch ($class) {
				case 1:
					$s = "Warrior";
					break;
				case 2:
					$s = "Paladin";
					break;
				case 3:
					$s = "Hunter";
					break;
				case 4:
					$s = "Rogue";
					break;
				case 5:
					$s = "Priest";
					break;
				case 6:
					$s = "Death Knight";
					break;
				case 7:
					$s = "Shaman";
					break;
				case 8:
					$s = "Mage";
					break;
				case 9:
					$s = "Warlock";
					break;
				case 10:
					$s = "Monk";
					break;
				case 11:
					$s = "Druid";
					break;
			}
			return $s;			
		}		

		function get_character_gender($gender) {
			$s = "";
			switch ($gender) {
				case 0:
					$s = "Male";
					break;
				case 1:
					$s = "Female";
					break;
			}
			return $s;
		}
		
		function get_character_faction($race) {
			$s = "";
			switch ($race) {
				case 1:
					$s = "Alliance";
					break;
				case 2:
					$s = "Horde";
					break;
				case 3:
					$s = "Alliance";
					break;
				case 4:
					$s = "Alliance";
					break;
				case 5:
					$s = "Horde";
					break;
				case 6:
					$s = "Horde";
					break;
				case 7:
					$s = "Alliance";
					break;
				case 8:
					$s = "Horde";
					break;
				case 9:
					$s = "Horde";
					break;
				case 10:
					$s = "Horde";
					break;
				case 11:
					$s = "Alliance";
					break;
				case 22:
					$s = "Alliance";
					break;
				case 24:
					$s = "Neutral";
					break;
				case 25:
					$s = "Alliance";
					break;
				case 26:
					$s = "Horde";
					break;
			}
			return $s;		
		}
		
		function get_character_race($race) {
			$s = "";
			switch ($race) {
				case 1:
					$s = "Human";
					break;
				case 2:
					$s = "Orc";
					break;
				case 3:
					$s = "Dwarf";
					break;
				case 4:
					$s = "Night Elf";
					break;
				case 5:
					$s = "Undead";
					break;
				case 6:
					$s = "Tauren";
					break;
				case 7:
					$s = "Gnome";
					break;
				case 8:
					$s = "Troll";
					break;
				case 9:
					$s = "Goblin";
					break;
				case 10:
					$s = "Blood Elf";
					break;
				case 11:
					$s = "Draenei";
					break;
				case 22:
					$s = "Worgen";
					break;
				case 24:
					$s = "Pandaren";
					break;
				case 25:
					$s = "Pandaren";
					break;
				case 26:
					$s = "Pandaren";
					break;
			}
			return $s;			
		}
		
	}

?>