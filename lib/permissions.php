<?php

class permissions {

	private $db;

	function __construct($dbhandle) {
		$this->db = $dbhandle;
	}

	public function isUserSuperadmin($user) {
		if($user->superadmin > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function isUserAdminForRoster($user, $roster_id) {
		if($user->superadmin > 0) {
			return true;
		} else {
			foreach($this->db->getUserRostersAdmin($user->id) as $checkRoster) {
				if($checkRoster->roster_id == $roster_id) {
					return true;
				}
			}
		}
	}

	public function isUserAssignedToRoster($user, $roster_id) {
		if($user->superadmin > 0) {
			return true;
		} else {
			foreach($this->db->getUserRosters($user->id) as $checkRoster) {
				if($checkRoster->roster_id == $roster_id) {
					return true;
				}
			}
		}
	}

}
