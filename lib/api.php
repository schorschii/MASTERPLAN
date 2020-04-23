<?php

class api {

	private $dbhandle;

	function __construct($dbhandle) {
		$this->dbhandle = $dbhandle;
	}

	public function checkActive() {
		if(boolval($this->dbhandle->getSetting('api_active')))
			return true;
		else
			die('API disabled');
	}

	public function checkAuth() {
		if(PHP_SAPI === 'cli')
			return true;

		$apiKey = $this->dbhandle->getSetting('api_key');
		if(isset($_GET['apikey']) && $_GET['apikey'] == $apiKey)
			return true;
		if(isset($_POST['apikey']) && $_POST['apikey'] == $apiKey)
			return true;

		foreach(explode(',', $this->dbhandle->getSetting('api_allowed_ips')) as $ip) {
			if(trim($ip) == $_SERVER['REMOTE_ADDR']) return true;
		}

		die('API authentication failed');
	}

}
