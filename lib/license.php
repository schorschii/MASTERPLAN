<?php

class license {

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
		if($this->userCount > self::FREE_USERS) {
			if(file_exists(self::LICENSE_FILE)) {
				$this->licenseValid = false;
				$fileContent = file_get_contents(self::LICENSE_FILE);
				$this->licenseContent = json_decode($fileContent, true);
				$this->parseLicenseContent();
			} else {
				$this->licenseText = 'Keine Lizenzdatei gefunden';
			}
		} else {
			$this->licenseValid = true;
			$this->licenseUsers = self::FREE_USERS;
			$this->licenseCompany = 'Evaluationslizenz';
			$this->licenseText = 'Sie verwenden die für 5 Benutzer kostenfreie Evaluationslizenz.';
		}
	}

	private function parseLicenseContent() {
		if(!isset($this->licenseContent['users'])
		|| !isset($this->licenseContent['valid_until'])) {
			$this->licenseText = 'Die hochgeladene Lizenzdatei ist keine valide MASTERPLAN-Lizenzdatei';
			return;
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
					$this->licenseText = "Ihre Lizenz für ".$this->licenseUsers." Benutzer ist bis zum ".strftime(DATE_FORMAT, $timeLicenseExpire)." gültig";
				} else {
					$this->licenseText = "Ihre derzeitige Benutzeranzahl (".$this->userCount.") übersteigt die lizenzierten ".$this->licenseUsers." Benutzer";
				}
			} else {
				$this->licenseText = "Ihre Lizenz ist am ".strftime(DATE_FORMAT, $timeLicenseExpire)." abgelaufen";
			}
		}
		else {
			$this->licenseText = "Die Signatur-Prüfung Ihrer Lizenzdatei ist fehlgeschlagen. Möglicherweise wurde sie manipuliert oder ist beschädigt.";
		}
	}

}
