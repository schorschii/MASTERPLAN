<?php

class browser {

	public $valid;
	public $message = '';

	function __construct() {
		$this->checkBrowser();
	}

	private function checkBrowser() {
		if($this->isInternetExplorer()) {
			$this->valid = false;
			$this->message = LANG['browser_not_supported'];
		}
		elseif($this->isFirefox()) {
			$this->valid = false;
			$this->message = LANG['browser_not_supported_calendar_field'];
		}
		elseif($this->isSafari()) {
			$this->valid = false;
			$this->message = LANG['browser_not_supported_calendar_field'];
		}
		else {
			$this->valid = true;
		}
	}

	private function isInternetExplorer() {
		$ua = $_SERVER["HTTP_USER_AGENT"];
		return (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0; rv:11.0') !== false));
	}

	private function isFirefox() {
		$ua = $_SERVER["HTTP_USER_AGENT"];
		return boolval(strpos($ua, 'Firefox'));
	}

	private function isSafari() {
		$ua = $_SERVER["HTTP_USER_AGENT"];
		return (strpos($ua, 'Safari') && !strpos($ua, 'Chrome'));
	}

	/* functions for detecting browsers and operating systems */
	public static function getOperatingSystem() {
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if(strpos($ua, 'Android'))
			return "Android";
		elseif(strpos($ua, 'iPhone') || strpos($ua, 'iPad'))
			return "iPhone";
		elseif(strpos($ua, 'Palm'))
			return "Palm";
		elseif(strpos($ua, 'Linux'))
			return "Linux";
		elseif(strpos($ua, 'Macintosh'))
			return "Macintosh";
		elseif(strpos($ua, 'Windows'))
			return "Windows";
		else
			return "Unknown";
	}

	/*** for detecting different versions
	$msie_7         = strpos($ua, 'MSIE 7.0') ? true : false;
	$msie_8         = strpos($ua, 'MSIE 8.0') ? true : false;
	$firefox_2      = strpos($ua, 'Firefox/2.0') ? true : false;
	$firefox_3      = strpos($ua, 'Firefox/3.0') ? true : false;
	$firefox_3_6    = strpos($ua, 'Firefox/3.6') ? true : false;
	$safari_2       = strpos($ua, 'Safari/419') ? true : false;    // Safari 2
	$safari_3       = strpos($ua, 'Safari/525') ? true : false;    // Safari 3
	$safari_3_1     = strpos($ua, 'Safari/528') ? true : false;    // Safari 3.1
	$safari_4       = strpos($ua, 'Safari/531') ? true : false;    // Safari 4
	*/

}
