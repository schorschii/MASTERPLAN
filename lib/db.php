<?php

class db {
	private $mysqli;
	private $statement;

	function __construct() {
		#mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // debug
		$link = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if($link->connect_error) {
			die(':-O !!! failed to establish database connection: ' . $link->connect_error);
		}
		$link->set_charset("utf8");
		$this->mysqli = $link;
	}

	public function getDbHandle() {
		return $this->mysqli;
	}
	public function getLastStatement() {
		return $this->statement;
	}

	public function beginTransaction() {
		return $this->mysqli->autocommit(false);
	}
	public function commitTransaction() {
		return $this->mysqli->commit();
	}
	public function rollbackTransaction() {
		return $this->mysqli->rollback();
	}

	public static function getResultObjectArray($result) {
		$resultArray = [];
		while($row = $result->fetch_object()) {
			$resultArray[] = $row;
		}
		return $resultArray;
	}

	public function existsSchema() {
		$sql = "SHOW TABLES LIKE 'User'";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		return ($result->num_rows == 1);
	}

	// User Operations
	public function getUserByLogin($login) {
		$sql = "SELECT * FROM User WHERE login = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('s', $login)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getUser($id) {
		$sql = "SELECT * FROM User WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getUsers() {
		$sql = "SELECT * FROM User ORDER BY ldap ASC, lastname ASC, firstname ASC, fullname ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getUsersByRoster($roster_id) {
		$sql = "SELECT u.* FROM UserToRoster ur
			INNER JOIN User u ON u.id = ur.user_id
			WHERE ur.roster_id = ?
			ORDER BY lastname ASC, firstname ASC, fullname ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $roster_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertUserLdap($superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $start_date, $description, $ldap, $color) {
		$sql = "INSERT INTO User (superadmin, login, firstname, lastname, fullname, email, phone, mobile, start_date, description, ldap, color) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('isssssssssis', $superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $start_date, $description, $ldap, $color)) return null;
		if(!$this->statement->execute()) return null;
		return $this->statement->insert_id;
	}
	public function updateUserLdap($id, $superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $description, $ldap) {
		$sql = "UPDATE User SET superadmin = ?, login = ?, firstname = ?, lastname = ?, fullname = ?, email = ?, phone = ?, mobile = ?, description = ?, ldap = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('issssssssii', $superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $description, $ldap, $id)) return null;
		return $this->statement->execute();
	}
	public function createUser($superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $birthday, $start_date, $id_no, $description, $ldap, $locked, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month, $color) {
		$sql = "INSERT INTO User (superadmin, login, firstname, lastname, fullname, email, phone, mobile, birthday, start_date, id_no, description, ldap, locked, max_hours_per_day, max_services_per_week, max_hours_per_week, max_hours_per_month, color)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('isssssssssssiiiiiis', $superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $birthday, $start_date, $id_no, $description, $ldap, $locked, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month, $color)) return null;
		if(!$this->statement->execute()) return null;
		return $this->statement->insert_id;
	}
	public function updateUser($id, $superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $birthday, $start_date, $id_no, $description, $ldap, $locked, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month, $color) {
		$sql = "UPDATE User
			SET superadmin = ?, login = ?, firstname = ?, lastname = ?, fullname = ?, email = ?, phone = ?, mobile = ?, birthday = ?, start_date = ?, id_no = ?, description = ?, ldap = ?, locked = ?, max_hours_per_day = ?, max_services_per_week = ?, max_hours_per_week = ?, max_hours_per_month = ?, color = ?
			WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('isssssssssssiiiiiisi', $superadmin, $login, $firstname, $lastname, $fullname, $email, $phone, $mobile, $birthday, $start_date, $id_no, $description, $ldap, $locked, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month, $color, $id)) return null;
		return $this->statement->execute();
	}
	public function updateUserPassword($id, $password) {
		$sql = "UPDATE User SET password = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('si', $password, $id)) return null;
		return $this->statement->execute();
	}
	public function removeUser($id) {
		$sql = "DELETE FROM User WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}
	public function getUserRosters($user_id) {
		$sql = "SELECT ur.*, r.id AS 'roster_id', r.title AS 'roster_title'
			FROM UserToRoster ur
			INNER JOIN Roster r ON ur.roster_id = r.id
			WHERE user_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertUserToRoster($user_id, $roster_id) {
		$sql = "INSERT INTO UserToRoster (user_id, roster_id) VALUES (?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('ii', $user_id, $roster_id)) return null;
		return $this->statement->execute();
	}
	public function removeUserToRosterByUser($user_id) {
		$sql = "DELETE FROM UserToRoster WHERE user_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		return $this->statement->execute();
	}
	public function removeUserToRosterByRoster($roster_id) {
		$sql = "DELETE FROM UserToRoster WHERE roster_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $roster_id)) return null;
		return $this->statement->execute();
	}
	public function getUserRostersAdmin($user_id) {
		$sql = "SELECT ur.*, r.title AS 'roster_title'
			FROM UserToRosterAdmin ur
			INNER JOIN Roster r ON ur.roster_id = r.id
			WHERE user_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertUserToRosterAdmin($user_id, $roster_id) {
		$sql = "INSERT INTO UserToRosterAdmin (user_id, roster_id) VALUES (?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('ii', $user_id, $roster_id)) return null;
		return $this->statement->execute();
	}
	public function removeUserToRosterAdminByUser($user_id) {
		$sql = "DELETE FROM UserToRosterAdmin WHERE user_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		return $this->statement->execute();
	}
	public function getUserRoles($user_id) {
		$sql = "SELECT ur.*, r.id AS 'role_id', r.title AS 'role_title'
			FROM UserToRole ur
			INNER JOIN Role r ON ur.role_id = r.id
			WHERE user_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertUserToRole($user_id, $role_id) {
		$sql = "INSERT INTO UserToRole (user_id, role_id) VALUES (?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('ii', $user_id, $role_id)) return null;
		return $this->statement->execute();
	}
	public function removeUserToRoleByRole($role_id) {
		$sql = "DELETE FROM UserToRole WHERE role_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $role_id)) return null;
		return $this->statement->execute();
	}
	public function getUserConstraints($user_id) {
		$sql = "SELECT uc.*, s.shortname, s.title
			FROM UserConstraint uc
			LEFT JOIN Service s ON s.id = uc.service_id
			WHERE uc.user_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function updateUserConstraint($id, $user_id, $service_id, $wd1, $wd2, $wd3, $wd4, $wd5, $wd6, $wd7, $comment) {
		$sql = "REPLACE INTO UserConstraint (id, user_id, service_id, wd1, wd2, wd3, wd4, wd5, wd6, wd7, comment)
			VALUES (?,?,?,?,?,?,?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('iiiiiiiiiis', $id, $user_id, $service_id, $wd1, $wd2, $wd3, $wd4, $wd5, $wd6, $wd7, $comment)) return null;
		return $this->statement->execute();
	}
	public function removeUserConstraint($id) {
		$sql = "DELETE FROM UserConstraint WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}

	// Role Operations
	public function getRoles() {
		$sql = "SELECT * FROM Role ORDER BY title ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getRole($id) {
		$sql = "SELECT * FROM Role WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function createRole($title, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month) {
		$sql = "INSERT INTO Role (title, max_hours_per_day, max_services_per_week, max_hours_per_week, max_hours_per_month) VALUES (?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('siiii', $title, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month)) return null;
		if(!$this->statement->execute()) return null;
		return $this->statement->insert_id;
	}
	public function updateRole($id, $title, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month) {
		$sql = "UPDATE Role SET title = ?, max_hours_per_day = ?, max_services_per_week = ?, max_hours_per_week = ?, max_hours_per_month = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('siiiii', $title, $max_hours_per_day, $max_services_per_week, $max_hours_per_week, $max_hours_per_month, $id)) return null;
		return $this->statement->execute();
	}
	public function removeRole($id) {
		$sql = "DELETE FROM Role WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}

	// Roster Operations
	public function getRosters() {
		$sql = "SELECT * FROM Roster ORDER BY title ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getRoster($id) {
		$sql = "SELECT * FROM Roster WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getRosterAdmins($roster_id) {
		$sql = "SELECT ura.*, u.login AS 'user_login', u.email AS 'user_email'
			FROM UserToRosterAdmin ura
			INNER JOIN User u ON u.id = ura.user_id
			WHERE ura.roster_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $roster_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getRosterUsers($roster_id) {
		$sql = "SELECT ur.*, u.login AS 'user_login', u.email AS 'user_email'
			FROM UserToRoster ur
			INNER JOIN User u ON u.id = ur.user_id
			WHERE ur.roster_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $roster_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertRoster($title, $autoplan_logic, $ignore_working_hours, $icsmail_sender_name, $icsmail_sender_address) {
		$sql = "INSERT INTO Roster (title, autoplan_logic, ignore_working_hours, icsmail_sender_name, icsmail_sender_address) VALUES (?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('siiss', $title, $autoplan_logic, $ignore_working_hours, $icsmail_sender_name, $icsmail_sender_address)) return null;
		return $this->statement->execute();
	}
	public function updateRoster($id, $title, $autoplan_logic, $ignore_working_hours, $icsmail_sender_name, $icsmail_sender_address) {
		$sql = "UPDATE Roster SET title = ?, autoplan_logic = ?, ignore_working_hours = ?, icsmail_sender_name = ?, icsmail_sender_address = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('siissi', $title, $autoplan_logic, $ignore_working_hours, $icsmail_sender_name, $icsmail_sender_address, $id)) return null;
		return $this->statement->execute();
	}
	public function removeRoster($id) {
		$sql = "DELETE FROM Roster WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}

	// Service Operations
	public function getServices() {
		$sql = "SELECT * FROM Service ORDER BY start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getServicesFromRoster($id) {
		$sql = "SELECT * FROM Service WHERE roster_id = ? ORDER BY start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getServicesByRosterAndDay($roster_id, $day) {
		$sql = "SELECT * FROM Service
			WHERE roster_id = ? AND (? BETWEEN date_start AND date_end)
			ORDER BY start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $roster_id, $day)) return null;
		if(!$this->statement->execute()) return null;
		// filter weekday
		$finalResult = [];
		$wd = date('N', strtotime($day));
		foreach(self::getResultObjectArray($this->statement->get_result()) as $s) {
			if($wd == 1 && $s->wd1 == 0) continue;
			if($wd == 2 && $s->wd2 == 0) continue;
			if($wd == 3 && $s->wd3 == 0) continue;
			if($wd == 4 && $s->wd4 == 0) continue;
			if($wd == 5 && $s->wd5 == 0) continue;
			if($wd == 6 && $s->wd6 == 0) continue;
			if($wd == 7 && $s->wd7 == 0) continue;
			$finalResult[] = $s;
		}
		return $finalResult;
	}
	public function getService($id) {
		$sql = "SELECT * FROM Service WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function createService($roster, $shortname, $title, $location, $employees, $start, $end, $date_start, $date_end, $color, $wd1, $wd2, $wd3, $wd4, $wd5, $wd6, $wd7) {
		$sql = "INSERT INTO Service
			(roster_id, shortname, title, location, employees, start, end, date_start, date_end, color, wd1, wd2, wd3, wd4, wd5, wd6, wd7)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('isssisssssiiiiiii', $roster, $shortname, $title, $location, $employees, $start, $end, $date_start, $date_end, $color, $wd1, $wd2, $wd3, $wd4, $wd5, $wd6, $wd7)) return null;
		return $this->statement->execute();
	}
	public function updateService($id, $roster, $shortname, $title, $location, $employees, $start, $end, $date_start, $date_end, $color, $wd1, $wd2, $wd3, $wd4, $wd5, $wd6, $wd7) {
		$sql = "UPDATE Service SET roster_id = ?, shortname = ?, title = ?, location = ?, employees = ?, start = ?, end = ?,
			date_start = ?, date_end = ?, color = ?, wd1 = ?, wd2 = ?, wd3 = ?, wd4 = ?, wd5 = ?, wd6 = ?, wd7 = ?
			WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('isssisssssiiiiiiii', $roster, $shortname, $title, $location, $employees, $start, $end, $date_start, $date_end, $color, $wd1, $wd2, $wd3, $wd4, $wd5, $wd6, $wd7, $id)) return null;
		return $this->statement->execute();
	}
	public function removeService($id) {
		$sql = "DELETE FROM Service WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}
	public function removeExpiredServices() {
		$sql = "DELETE FROM Service WHERE date_end < NOW()";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		return $this->statement->execute();
	}

	// Planned Service Operations
	public function getPlannedService($id) {
		$sql = "SELECT ps.*, s.title AS 'service_title', s.shortname AS 'service_shortname',
			s.start AS 'service_start', s.end AS 'service_end', s.location AS 'service_location',
			s.roster_id AS 'service_roster_id', u.fullname AS 'user_fullname', u.color as 'user_color', u.email AS 'user_email'
			FROM PlannedService ps
			INNER JOIN Service s ON ps.service_id = s.id
			INNER JOIN User u ON ps.user_id = u.id
			WHERE ps.id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getPlannedServicesWithUserByRosterAndServiceAndDay($roster_id, $service_id, $day) {
		$sql = "SELECT ps.*, s.title AS 'service_title', s.shortname AS 'service_shortname',
			s.start AS 'service_start', s.end AS 'service_end', s.location AS 'service_location',
			s.roster_id AS 'service_roster_id', u.fullname AS 'user_fullname', u.color as 'user_color', u.email AS 'user_email'
			FROM PlannedService ps
			INNER JOIN Service s ON ps.service_id = s.id
			INNER JOIN User u ON ps.user_id = u.id
			WHERE s.roster_id = ? AND s.id = ? AND ps.day = ?
			ORDER BY s.start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('iis', $roster_id, $service_id, $day)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getPlannedServicesWithUserByRosterAndDay($roster_id, $day) {
		$sql = "SELECT ps.*, s.title AS 'service_title', s.shortname AS 'service_shortname',
			s.start AS 'service_start', s.end AS 'service_end', s.location AS 'service_location',
			s.roster_id AS 'service_roster_id', u.fullname AS 'user_fullname', u.color as 'user_color', u.email AS 'user_email'
			FROM PlannedService ps
			INNER JOIN Service s ON ps.service_id = s.id
			INNER JOIN User u ON ps.user_id = u.id
			WHERE s.roster_id = ? AND ps.day = ?
			ORDER BY s.start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $roster_id, $day)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getPlannedServicesWithUserByRoster($roster_id) {
		$sql = "SELECT ps.*, s.title AS 'service_title', s.shortname AS 'service_shortname',
			s.start AS 'service_start', s.end AS 'service_end', s.location AS 'service_location',
			s.roster_id AS 'service_roster_id', u.fullname AS 'user_fullname', u.color as 'user_color', u.email AS 'user_email'
			FROM PlannedService ps
			INNER JOIN Service s ON ps.service_id = s.id
			INNER JOIN User u ON ps.user_id = u.id
			WHERE s.roster_id = ?
			ORDER BY s.start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $roster_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getPlannedServicesByUserAndDay($user_id, $day) {
		$sql = "SELECT ps.*, s.title AS 'service_title', s.shortname AS 'service_shortname',
			s.start AS 'service_start', s.end AS 'service_end', s.roster_id AS 'service_roster_id', s.color AS 'service_color', s.employees AS 'service_employees'
			FROM PlannedService ps
			INNER JOIN Service s ON ps.service_id = s.id
			WHERE ps.user_id = ? AND ps.day = ?
			ORDER BY s.start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $user_id, $day)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getPlannedServicesByUser($user_id) {
		$sql = "SELECT ps.*, s.title AS 'service_title', s.shortname AS 'service_shortname', s.location AS 'service_location',
			s.start AS 'service_start', s.end AS 'service_end', s.color AS 'service_color', s.roster_id AS 'service_roster_id',
			u.fullname AS 'user_fullname', u.color as 'user_color', u.email AS 'user_email'
			FROM PlannedService ps
			INNER JOIN Service s ON ps.service_id = s.id
			INNER JOIN User u ON ps.user_id = u.id
			WHERE ps.user_id = ?
			ORDER BY ps.day ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getFuturePlannedServicesByUser($user_id) {
		$sql = "SELECT ps.*, s.title AS 'service_title', s.shortname AS 'service_shortname',
			s.start AS 'service_start', s.end AS 'service_end', s.color AS 'service_color', s.roster_id AS 'service_roster_id'
			FROM PlannedService ps
			INNER JOIN Service s ON ps.service_id = s.id
			WHERE ps.user_id = ? AND ps.day > NOW()
			ORDER BY ps.day ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function setPlannedServiceSent($id, $icsmail_sent) {
		$sql = "UPDATE PlannedService SET icsmail_sent = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('ii', $icsmail_sent, $id)) return null;
		return $this->statement->execute();
	}
	public function updatePlannedService($id, $day, $service_id, $user_id) {
		$sql = "REPLACE INTO PlannedService (id, day, service_id, user_id) VALUES (?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('isii', $id, $day, $service_id, $user_id)) return null;
		return $this->statement->execute();
	}
	public function removePlannedService($id) {
		$sql = "DELETE FROM PlannedService WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}
	public function removePlannedServicesOlderThan($day) {
		$sql = "DELETE FROM PlannedService WHERE day <= ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('s', $day)) return null;
		return $this->statement->execute();
	}
	public function getPlannedServiceNotes($service_id, $day) {
		$sql = "SELECT * FROM PlannedServiceNote
			WHERE day = ? AND service_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('si', $day, $service_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function updatePlannedServiceNote($service_id, $day, $note) {
		$sql = "DELETE FROM PlannedServiceNote WHERE day = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		if(!$this->statement->bind_param('s', $day)) return false;
		if(!$this->statement->execute()) return false;
		if(trim($note) != "") {
			$sql = "INSERT INTO PlannedServiceNote (day, service_id, note) VALUES (?,?,?)";
			if(!$this->statement = $this->mysqli->prepare($sql)) return false;
			if(!$this->statement->bind_param('sis', $day, $service_id, $note)) return false;
			return $this->statement->execute();
		}
		return true;
	}
	public function getPlannedServiceFile($id) {
		$sql = "SELECT * FROM PlannedServiceFile WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getPlannedServiceFilesByServiceIdAndDay($service_id, $day) {
		$sql = "SELECT * FROM PlannedServiceFile WHERE day = ? AND service_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		if(!$this->statement->bind_param('si', $day, $service_id)) return false;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function createPlannedServiceFile($service_id, $day, $title, $file) {
		$null = null;
		$sql = "INSERT INTO PlannedServiceFile (day, service_id, title, file) VALUES (?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		if(!$this->statement->bind_param('sisb', $day, $service_id, $title, $null)) return false;
		if(!$this->statement->send_long_data(3, $file)) return false;
		return $this->statement->execute();
	}
	public function removePlannedServiceFile($id) {
		$sql = "DELETE FROM PlannedServiceFile WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		if(!$this->statement->bind_param('i', $id)) return false;
		return $this->statement->execute();
	}
	public function getResources() {
		$sql = "SELECT * FROM Resource ORDER BY type, title ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getResource($id) {
		$sql = "SELECT * FROM Resource WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function removeResource($id) {
		$sql = "DELETE FROM Resource WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}
	public function createResource($type, $title, $description, $icon, $color) {
		$sql = "INSERT INTO Resource (type, title, description, icon, color) VALUES (?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		if(!$this->statement->bind_param('sssss', $type, $title, $description, $icon, $color)) return false;
		return $this->statement->execute();
	}
	public function updateResource($id, $type, $title, $description, $icon, $color) {
		$sql = "UPDATE Resource SET type = ?, title = ?, description = ?, icon = ?, color = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('sssssi', $type, $title, $description, $icon, $color, $id)) return null;
		return $this->statement->execute();
	}
	public function getPlannedServiceResourcesByServiceAndDay($service_id, $day) {
		$sql = "SELECT psr.*, r.title AS 'resource_title', r.description AS 'resource_description',
			r.icon AS 'resource_icon', r.type AS 'resource_type', r.color AS 'resource_color'
			FROM PlannedServiceResource psr
			INNER JOIN Resource r ON psr.resource_id = r.id
			WHERE psr.service_id = ? AND psr.day = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $service_id, $day)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getPlannedServiceResourcesByResourceAndDay($resource_id, $day) {
		$sql = "SELECT psr.*, r.title AS 'resource_title', r.description AS 'resource_description',
			r.icon AS 'resource_icon', r.type AS 'resource_type', r.color AS 'resource_color',
			s.id AS 'service_id', s.start AS 'service_start', s.end AS 'service_end'
			FROM PlannedServiceResource psr
			INNER JOIN Resource r ON psr.resource_id = r.id
			INNER JOIN Service s ON psr.service_id = s.id
			WHERE psr.resource_id = ? AND psr.day = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $resource_id, $day)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getPlannedServiceResource($id) {
		$sql = "SELECT * FROM PlannedServiceResource WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function createPlannedServiceResource($day, $service_id, $resource_id) {
		$sql = "INSERT INTO PlannedServiceResource (day, service_id, resource_id) VALUES (?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		if(!$this->statement->bind_param('sii', $day, $service_id, $resource_id)) return false;
		return $this->statement->execute();
	}
	public function removePlannedServiceResource($id) {
		$sql = "DELETE FROM PlannedServiceResource WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		if(!$this->statement->bind_param('i', $id)) return false;
		return $this->statement->execute();
	}

	// Swap Service Operations
	public function getSwapServices() {
		$sql = "SELECT ss.*, s.shortname AS 'service_shortname', s.title AS 'service_title',
			s.color AS 'service_color', s.start AS 'service_start', s.end AS 'service_end',
			s.id AS 'service_id', s.roster_id AS 'service_roster_id', ps.day AS 'planned_service_day',
			u.fullname AS 'user_fullname', u.id AS 'user_id'
			FROM SwapService ss
			INNER JOIN PlannedService ps ON ss.planned_service_id = ps.id
			INNER JOIN Service s ON s.id = ps.service_id
			INNER JOIN User u ON ps.user_id = u.id";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getSwapService($id) {
		$sql = "SELECT ss.*, s.shortname AS 'service_shortname', s.title AS 'service_title',
			s.color AS 'service_color', s.start AS 'service_start', s.end AS 'service_end',
			s.id AS 'service_id', s.roster_id AS 'service_roster_id', ps.day AS 'planned_service_day',
			ps.id AS 'planned_service_id', u.fullname AS 'user_fullname', u.id AS 'user_id'
			FROM SwapService ss
			INNER JOIN PlannedService ps ON ss.planned_service_id = ps.id
			INNER JOIN Service s ON s.id = ps.service_id
			INNER JOIN User u ON ps.user_id = u.id
			WHERE ss.id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getSwapServiceByPlannedServiceId($id) {
		$sql = "SELECT ss.*, s.shortname AS 'service_shortname', s.title AS 'service_title',
			s.color AS 'service_color', s.start AS 'service_start', s.end AS 'service_end',
			s.id AS 'service_id', s.roster_id AS 'service_roster_id', ps.day AS 'planned_service_day',
			ps.id AS 'planned_service_id', u.fullname AS 'user_fullname', u.id AS 'user_id'
			FROM SwapService ss
			INNER JOIN PlannedService ps ON ss.planned_service_id = ps.id
			INNER JOIN Service s ON s.id = ps.service_id
			INNER JOIN User u ON ps.user_id = u.id
			WHERE ss.planned_service_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function updateSwapService($id, $planned_service_id, $comment) {
		$sql = "REPLACE INTO SwapService (id, planned_service_id, comment) VALUES (?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('iis', $id, $planned_service_id, $comment)) return null;
		return $this->statement->execute();
	}
	public function removeSwapService($id) {
		$sql = "DELETE FROM SwapService WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}
	public function removeSwapServicesOlderThan($day) {
		$sql = "DELETE ss FROM SwapService ss
			INNER JOIN PlannedService ps ON ps.id = ss.planned_service_id
			WHERE ps.day <= ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('s', $day)) return null;
		return $this->statement->execute();
	}

	// Absence Operations
	public function getAbsence($id) {
		$sql = "SELECT * FROM Absence WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getUnapprovedAbsencesByUser($user_id) {
		$sql = "SELECT * FROM Absence WHERE user_id = ? ORDER BY start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		$consolidatedAbsences = [];
		$aplan = new autoplan($this);
		foreach( self::getResultObjectArray($this->statement->get_result()) as $absence) {
			if(!$aplan->isAbsenceApproved($absence))
				$consolidatedAbsences[] = $absence;
		}
		return $consolidatedAbsences;
	}
	public function getAbsencesByUser($user_id) {
		$sql = "SELECT a.*, at.shortname AS 'absent_type_shortname', at.title AS 'absent_type_title',
			at.color AS 'absent_type_color'
			FROM Absence a
			INNER JOIN AbsentType at ON a.absent_type_id = at.id
			WHERE a.user_id = ?
			ORDER BY a.start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $user_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getFutureAbsencesByUser($user_id) {
		$now = date('Y-m-d', strtotime('-1 day'));
		$sql = "SELECT a.*, at.shortname AS 'absent_type_shortname', at.title AS 'absent_type_title',
			at.color AS 'absent_type_color'
			FROM Absence a
			INNER JOIN AbsentType at ON a.absent_type_id = at.id
			WHERE a.user_id = ? AND a.end > ?
			ORDER BY a.start ASC";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $user_id, $now)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function updateAbsence($id, $user_id, $type, $start, $end, $comment, $approved1) {
		$sql = "REPLACE INTO Absence (id, user_id, absent_type_id, start, end, comment, approved1) VALUES (?,?,?,?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('iiisssi', $id, $user_id, $type, $start, $end, $comment, $approved1)) return null;
		if(!$this->statement->execute()) return null;
		return $this->statement->insert_id;
	}
	public function approveAbsence1($id, $approved1, $approved1_by_user_id) {
		$sql = "UPDATE Absence SET approved1 = ?, approved1_by_user_id = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('iii', $approved1, $approved1_by_user_id, $id)) return null;
		return $this->statement->execute();
	}
	public function approveAbsence2($id, $approved2, $approved2_by_user_id) {
		$sql = "UPDATE Absence SET approved2 = ?, approved2_by_user_id = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('iii', $approved2, $approved2_by_user_id, $id)) return null;
		return $this->statement->execute();
	}
	public function removeAbsence($id) {
		$sql = "DELETE FROM Absence WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}
	public function removeAbsencesOlderThan($day) {
		$sql = "DELETE FROM Absence WHERE end <= ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('s', $day)) return null;
		return $this->statement->execute();
	}

	// Roster Release Operations
	public function getReleasedPlansByRoster($roster_id) {
		$sql = "SELECT * FROM ReleasedPlan WHERE roster_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $roster_id)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getReleasedPlansByRosterAndDay($roster_id, $day) {
		$sql = "SELECT * FROM ReleasedPlan WHERE roster_id = ? AND day = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $roster_id, $day)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function updateReleasedPlan($id, $roster_id, $day) {
		$sql = "REPLACE INTO ReleasedPlan (id, roster_id, day) VALUES (?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('iis', $id, $roster_id, $day)) return null;
		return $this->statement->execute();
	}
	public function removeReleasedPlan($roster_id, $day) {
		$sql = "DELETE FROM ReleasedPlan WHERE roster_id = ? AND day = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('is', $roster_id, $day)) return null;
		return $this->statement->execute();
	}

	// Absent Types Operations
	public function getAbsentTypes() {
		$sql = "SELECT * FROM AbsentType";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getAbsentType($id) {
		$sql = "SELECT * FROM AbsentType WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function createAbsentType($shortname, $title, $color) {
		$sql = "INSERT INTO AbsentType (shortname, title, color) VALUES (?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('sss', $shortname, $title, $color)) return null;
		if(!$this->statement->execute()) return null;
		return $this->statement->insert_id;
	}
	public function updateAbsentType($id, $shortname, $title, $color) {
		$sql = "UPDATE AbsentType SET shortname = ?, title = ?, color = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('sssi', $shortname, $title, $color, $id)) return null;
		return $this->statement->execute();
	}
	public function removeAbsentType($id) {
		$sql = "DELETE FROM AbsentType WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}

	// Settings Operations
	public function getSetting($key, $default=null) {
		$sql = "SELECT value FROM Setting WHERE setting = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('s', $key)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		#if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row->value;
		}
		return $default;
	}
	public function updateSetting($setting, $value) {
		$sql = "REPLACE INTO Setting (setting, value) VALUES (?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('ss', $setting, $value)) return null;
		return $this->statement->execute();
	}

	// Holiday Operations
	public function getHolidays() {
		$sql = "SELECT h.*, s.shortname AS 'service_shortname', s.title AS 'service_title'
			FROM Holiday h
			LEFT JOIN Service s ON h.service_id = s.id";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getHoliday($id) {
		$sql = "SELECT * FROM Holiday WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function updateHoliday($id, $title, $day, $service_id) {
		$sql = "REPLACE INTO Holiday (id, title, day, service_id) VALUES (?,?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('issi', $id, $title, $day, $service_id)) return null;
		if(!$this->statement->execute()) return null;
		return $this->statement->insert_id;
	}
	public function removeHoliday($id) {
		$sql = "DELETE FROM Holiday WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}

}
