<?php

class tools {

	public static function isValidEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function shortText($str, $chars = 50) {
		return strlen($str) > $chars ? substr($str,0,$chars)."..." : $str;
	}

	public function startsWith($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	public function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}

}
