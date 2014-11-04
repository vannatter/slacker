<?php

	///////////////////////////////////////////////////////////////////
	/// Slacker: a simple post-back bot for Slack
	/// http://github.com/vannatter/slacker
	///////////////////////////////////////////////////////////////////
	
	require("config/slacker.php");
	require("slacker.class.php");
	
	function __autoload($className) {
		$filename = "plugins/" . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}

	$plugin_name = trim(strtolower(strtok($_REQUEST['text'], ' ')));
	if (class_exists($plugin_name)) {
		$slacker = new $plugin_name;
		echo $slacker->output();
	} else {
		echo "@" . $_REQUEST['user_name'] . ", unknown command.";
	}

?>