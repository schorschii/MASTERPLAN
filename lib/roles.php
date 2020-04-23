<?php

class roles {
	private $dbhandle;

	function __construct($dbhandle) {
		$this->dbhandle = $dbhandle;
	}

	function getUserRole($id) {
		$user = $this->dbhandle->getUser($id);
		if($user == null) return null;
		foreach($this->dbhandle->getUserRoles($user->id) as $ur) {
			$role = $this->dbhandle->getRole($ur->role_id);
			return $role;
		}
		return null;
	}

	function updateRoleAffiliation() {
		foreach($this->dbhandle->getUsers() as $u) {
			foreach($this->dbhandle->getUserRoles($u->id) as $ur) {
				$role = $this->dbhandle->getRole($ur->role_id);
				$this->dbhandle->updateUser(
					$u->id,
					$u->superadmin,
					$u->login,
					$u->firstname,
					$u->lastname,
					$u->fullname,
					$u->email,
					$u->phone,
					$u->mobile,
					$u->birthday,
					$u->start_date,
					$u->id_no,
					$u->description,
					$u->ldap,
					$u->locked,
					$role->max_hours_per_day,
					$role->max_services_per_week,
					$role->max_hours_per_week,
					$role->max_hours_per_month,
					$u->color
				);
				break;
			}
		}
	}
}
