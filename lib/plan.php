<?php

class plan {
	private $dbhandle;

	function __construct($dbhandle) {
		$this->dbhandle = $dbhandle;
	}

	public function getConsolidatedServicesByRosterAndDay($roster_id, $day) {
		// check if service is canceled because of a defined holiday
		$services = [];
		$holidays = $this->dbhandle->getHolidays();
		foreach($this->dbhandle->getServicesByRosterAndDay($roster_id, $day) as $s) {
			if(count($holidays) == 0) {
				$services[] = $s;
			} else {
				foreach($holidays as $holiday) {
					if(!($holiday->day == $day && ($holiday->service_id == null || $holiday->service_id == $s->id))) {
						$services[] = $s;
						break;
					}
				}
			}
		}
		return $services;
	}

	public function removeAssignment($id) {
		$ps = $this->dbhandle->getPlannedService($id);
		if($ps == null) return false;

		$sendCancelMail = false;
		if($ps->icsmail_sent != null && $ps->icsmail_sent != 0) $sendCancelMail = true;

		if(!$this->dbhandle->removePlannedService($ps->id)) {
			return false;
		} else {
			if($sendCancelMail) {
				$this->sendIcsMail($ps, true);
			}
			return true;
		}
	}

	public function sendIcsMail($ps, $cancel) {
		if(tools::isValidEmail($ps->user_email)) {
			$icsDomain = $this->dbhandle->getSetting('ics_domain');
			$icsRoster = $this->dbhandle->getRoster($ps->service_roster_id);

			$body = texttemplate::processTemplate('icsmail_text.txt', null);
			if($cancel) $body = texttemplate::processTemplate('icsmail_text_cancel.txt', null);

			$ics = ics::compileIcsBody(
				$ps->id,
				$icsDomain,
				$icsRoster->icsmail_sender_name,
				$icsRoster->icsmail_sender_address,
				$ps->user_fullname, $ps->user_email,
				strtotime($ps->day.' '.$ps->service_start),
				strtotime($ps->day.' '.$ps->service_end),
				$ps->service_shortname,
				$ps->service_title,
				$ps->service_location,
				$cancel
			);
			$success = ics::sendIcsMail(
				$icsRoster->icsmail_sender_name,
				$icsRoster->icsmail_sender_address,
				$ps->user_email,
				$ps->service_shortname,
				$body, $ics
			);
			if($cancel) {
				$this->dbhandle->setPlannedServiceSent($ps->id, 0);
			} else {
				$this->dbhandle->setPlannedServiceSent($ps->id, 1);
			}
			return $success;
		} else {
			return false;
		}
	}

}
