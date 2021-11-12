<?php

class mailer {
	private $dbhandle;

	function __construct($db) {
		$this->dbhandle = $db;
	}

	function mailNewSwap($roster_id) {
		// send mail to all roster users
		foreach($this->dbhandle->getRosterUsers($roster_id) as $u) {
			if(!tools::isValidEmail($u->user_email)) continue;
			$body = texttemplate::processTemplate('mail_new_swap_service.txt', null);
			mail($u->user_email, LANG['new_service_swap_request'], $body);
		}
	}

	function mailNewAbsence($userid) {
		// send mail to all roster admins for approval
		foreach($this->dbhandle->getUserRosters($userid) as $ur) {
			foreach($this->dbhandle->getRosterAdmins($ur->roster_id) as $u) {
				if(!tools::isValidEmail($u->user_email)) continue;
				$body = texttemplate::processTemplate('mail_new_absence.txt', null);
				mail($u->user_email, LANG['new_absence_request'], $body);
			}
		}
	}

	function mailAbsenceApproved1($absence_id) {
		$absence = $this->dbhandle->getAbsence($absence_id);
		$user = $this->dbhandle->getUser($absence->user_id);
		if(tools::isValidEmail($user->email)) {
			$body = texttemplate::processTemplate('mail_absence_approved1.txt', null);
			mail($user->email, LANG['absence_request_confirmed'], $body);
		}
	}

	function mailAbsenceApproved2($absence_id) {
		$absence = $this->dbhandle->getAbsence($absence_id);
		$user = $this->dbhandle->getUser($absence->user_id);
		if(tools::isValidEmail($user->email)) {
			$body = texttemplate::processTemplate('mail_absence_approved2.txt', null);
			mail($user->email, LANG['absence_request_approved'], $body);
		}
	}

	function mailAbsenceDeclined($absence_id) {
		$absence = $this->dbhandle->getAbsence($absence_id);
		$user = $this->dbhandle->getUser($absence->user_id);
		if(tools::isValidEmail($user->email)) {
			$body = texttemplate::processTemplate('mail_absence_declined.txt', null);
			mail($user->email, LANG['absence_request_declined'], $body);
		}
	}
}
