<?php

class license {

	/* LEGAL WARNING
	   It is not allowed to modify this file in order to bypass license checks.
	   I decided to not use obfuscation techniques because they suck, so yeah, it's technically easy to bypass the check.
	   Please be so kind and support further development by purchasing licenses from https://georg-sieber.de
	*/

	const FREE_USERS          = 5;
	const LICENSE_FILE        = TMP_FILES.'/'.'license';

	private $licenseContent   = [];
	private $userCount        = 0;

	public $licenseValid      = false;
	public $licenseCompany    = '';
	public $licenseUsers      = 0;
	public $licenseExpireTime = 0;
	public $licenseText       = '';

	function __construct($userCount) {
		$this->userCount = $userCount;
		if(file_exists(self::LICENSE_FILE)) {
			$this->licenseValid = false;
			$fileContent = file_get_contents(self::LICENSE_FILE);
			$this->licenseContent = json_decode($fileContent, true);
			if(!$this->parseLicenseContent())
				$this->checkEvalLicense();
		} else {
			if(!$this->checkEvalLicense())
				$this->licenseText = LANG['no_license_file_found'];
		}
	}

	private function checkEvalLicense() {
		if($this->userCount > self::FREE_USERS) {
			return false;
		} else {
			$this->licenseValid = true;
			$this->licenseUsers = self::FREE_USERS;
			$this->licenseCompany = LANG['evaluation_license'];
			$this->licenseText = LANG['you_are_using_the_evaluation_license'];
			return true;
		}
	}

	private function parseLicenseContent() {
		if(!isset($this->licenseContent['users'])
		|| !isset($this->licenseContent['valid_until'])) {
			$this->licenseText = LANG['invalid_license_file'];
			return false;
		}

		$this->licenseCompany = $this->licenseContent['company'];
		$this->licenseUsers = intval($this->licenseContent['users']);
		$this->licenseExpireTime = $this->licenseContent['valid_until'];

		$checkStr = $this->licenseCompany.$this->licenseUsers.$this->licenseExpireTime;
		$checkSum = md5($checkStr);
		$signature = base64_decode($this->licenseContent['signature']);
		$result = openssl_verify($checkSum, $signature, PUBKEY);

		if($result) {
			$timeLicenseExpire = $this->licenseContent['valid_until'];
			if($timeLicenseExpire > time()) {
				if($this->userCount <= $this->licenseUsers) {
					$this->licenseValid = true;
					$this->licenseText = str_replace('%1', $this->licenseUsers, str_replace('%2', strftime(DATE_FORMAT, $timeLicenseExpire), LANG['license_of_users_is_valid_to']));
					return true;
				} else {
					$this->licenseText = str_replace('%1', $this->userCount, str_replace('%2', $this->licenseUsers, LANG['user_count_exeeds_license_limit']));
					return false;
				}
			} else {
				$this->licenseText = str_replace('%1', strftime(DATE_FORMAT, $timeLicenseExpire), LANG['license_expired_on']);
				return false;
			}
		} else {
			$this->licenseText = LANG['invalid_license_file'];
			return false;
		}
	}

}
