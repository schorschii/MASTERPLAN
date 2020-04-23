<?php

class autoplan {
	private $dbhandle;

	function __construct($dbhandle) {
		$this->dbhandle = $dbhandle;
	}

	public static function getServiceWorkTime($service) {
		return round((strtotime($service->end) - strtotime($service->start)) / 60 / 60, 2);
	}

	public function isAbsenceApproved($absence) {
		$absence_confirmation_required = intval($this->dbhandle->getSetting('absence_confirmation_required'));
		if($absence_confirmation_required == 1) {
			if($absence->approved1 > 0)
				return true;
		} elseif($absence_confirmation_required == 2) {
			if($absence->approved1 > 0 && $absence->approved2 > 0)
				return true;
		} else {
			return true;
		}
		return false;
	}

	public function getWorkByUserAndTimespan($user_id, $timeStart, $timeEnd) {
		$services = 0;
		$hours = 0;
		for($i = $timeStart; $i <= $timeEnd; $i = $i + 86400) {
			foreach($this->dbhandle->getPlannedServicesByUserAndDay($user_id, date('Y-m-d', $i)) as $ps) {
				$services ++;
				$hours += round((strtotime($ps->service_end) - strtotime($ps->service_start)) / 60 / 60, 2);
			}
		}
		return [
			'services' => $services,
			'hours' => $hours
		];
	}

	public function checkUserConstraintOrAbsent($user_id, $service_id, $day) {
		// check user constraints
		$wd = date('N', strtotime($day));
		foreach($this->dbhandle->getUserConstraints($user_id) as $uc) {
			if($uc->service_id == null || $uc->service_id = $service_id) {
				if($wd == 1 && $uc->wd1 == 1) return false;
				if($wd == 2 && $uc->wd2 == 1) return false;
				if($wd == 3 && $uc->wd3 == 1) return false;
				if($wd == 4 && $uc->wd4 == 1) return false;
				if($wd == 5 && $uc->wd5 == 1) return false;
				if($wd == 6 && $uc->wd6 == 1) return false;
				if($wd == 7 && $uc->wd7 == 1) return false;
			}
		}
		// check absences
		$timeDay = strtotime($day);
		foreach($this->dbhandle->getAbsencesByUser($user_id) as $a) {
			if(!$this->isAbsenceApproved($a)) continue;
			if(strtotime($a->start) <= $timeDay && strtotime($a->end) >= $timeDay) return false;
		}
		return true;
	}

	public function checkUserAlreadyAssignedToOtherService($user_id, $service_id, $day) {
		// check if user is already assigned to an other service at the same time
		$service = $this->dbhandle->getService($service_id);
		if($service == null) return false;
		$timeStart = strtotime($day.' '.$service->start);
		$timeEnd = strtotime($day.' '.$service->end);
		foreach($this->dbhandle->getPlannedServicesByUserAndDay($user_id, $day) as $ps) {
			$time2Start = strtotime($day.' '.$ps->service_start);
			$time2End = strtotime($day.' '.$ps->service_end);
			if(($timeStart == $time2Start)
			|| ($time2Start < $timeStart && $timeStart < $time2End)
			|| ($timeStart < $time2Start && $time2Start < $timeEnd)) {
				#echo $ps->service_id;
				return false;
			}
		}
		return true;
	}

	public function checkUserRestTime($user_id, $service_id, $day) {
		// check if user had a late shift yesterday (rest time)
		$restTimeHours = $this->dbhandle->getSetting('rest_period') ?? -1;
		if($restTimeHours < 1) return true; // no rest time check

		// get new service
		$service = $this->dbhandle->getService($service_id);
		if($service == null) return false;
		$timeStart = strtotime($day.' '.$service->start);

		// check all yesterdays services
		$strDayYesterday = date('Y-m-d', strtotime('-1 day', strtotime($day)));
		foreach($this->dbhandle->getPlannedServicesByUserAndDay($user_id, $strDayYesterday) as $ps) {
			$timeEnd = strtotime($strDayYesterday.' '.$ps->service_end);
			$span = ($timeStart-$timeEnd)/3600;
			if($span < $restTimeHours) {
				#echo date('Y-m-d H:i', $timeStart).' '.date('Y-m-d H:i', $timeEnd).' '.$span; // debug
				return false;
			}
		}
		return true;
	}

	public function getPotentialUsers($roster_id, $strWeek, $strDay, $service_id, $random=false) {
		$roster = $this->dbhandle->getRoster($roster_id);
		$workHours = self::getServiceWorkTime($this->dbhandle->getService($service_id));
		$users = [];
		foreach($this->dbhandle->getUsersByRoster($roster_id) as $u) {
			// special case: ignore working hours?
			if($roster->ignore_working_hours === 1) {
				$userUtilization = $this->getUserUtilization($u->id, $strWeek, $strDay);
			} else {
				$userUtilization = $this->canUserHandleWorkHoursInWeek($u->id, $workHours, $strWeek, $strDay);
			}

			if($userUtilization === false) {
				continue;
			}
			if($this->checkUserConstraintOrAbsent($u->id, $service_id, $strDay)) {
				if($this->checkUserAlreadyAssignedToOtherService($u->id, $service_id, $strDay)) {
					if($this->checkUserRestTime($u->id, $service_id, $strDay)) {
						$users[] = $userUtilization;
					}
				}
			}
		}
		if($random) {
			shuffle($users);
		} else {
			usort($users, ['autoplan','sortUsersByUtilization']);
		}
		return $users;
	}

	public function getUserUtilization($user_id, $strWeek, $strDay) {
		$u = $this->dbhandle->getUser($user_id);

		$day = $this->getWorkByUserAndTimespan(
			$u->id,
			strtotime($strDay),
			strtotime($strDay)
		);
		$week = $this->getWorkByUserAndTimespan(
			$u->id,
			strtotime($strWeek.' +0 day'),
			strtotime($strWeek.' +6 day')
		);
		$month = $this->getWorkByUserAndTimespan(
			$u->id,
			strtotime(date('Y-m-01', strtotime($strWeek))),
			strtotime(date('Y-m-t', strtotime($strWeek)))
		);

		$utilization_month_hours = @($month['hours']/$u->max_hours_per_month);
		$utilization_week_services = @($week['services']/$u->max_services_per_week);
		$utilization_week_hours = @($week['hours']/$u->max_hours_per_week);

		return [
			'id' => $u->id,

			'max_month_hours' => $u->max_hours_per_month,
			'max_week_services' => $u->max_services_per_week,
			'max_week_hours' => $u->max_hours_per_week,
			'max_day_hours' => $u->max_hours_per_day,

			'current_month_hours' => $month['hours'],
			'current_week_services' => $week['services'],
			'current_week_hours' => $week['hours'],
			'current_day_hours' => $day['hours'],

			'utilization_month_hours' => $utilization_month_hours,
			'utilization_week_services' => $utilization_week_services,
			'utilization_week_hours' => $utilization_week_hours,
			'utilization' => ($utilization_month_hours+$utilization_week_services+$utilization_week_hours)/3
		];
	}

	public function canUserHandleWorkHoursInWeek($user_id, $workHours, $strWeek, $strDay) {
		$u = $this->getUserUtilization($user_id, $strWeek, $strDay);
		if($u['max_month_hours'] >= 0) {
			if($u['current_month_hours']+$workHours > $u['max_month_hours']) {
				#echo "month hours";
				return false;
			}
		}
		if($u['max_week_services'] >= 0) {
			if($u['current_week_services']+1 > $u['max_week_services']) {
				#echo "week services";
				return false;
			}
		}
		if($u['max_week_hours'] >= 0) {
			if($u['current_week_hours']+$workHours > $u['max_week_hours']) {
				#echo "week hours";
				return false;
			}
		}
		if($u['max_day_hours'] >= 0) {
			if($u['current_day_hours']+$workHours > $u['max_day_hours']) {
				#echo "day hours";
				return false;
			}
		}
		return $u;
	}

	private static function sortUsersByUtilization($a, $b) {
		if($a['utilization'] == $b['utilization']) {
			return 0;
		}
		return ($a['utilization'] < $b['utilization']) ? -1 : 1;
	}

	/// Resource Functions ///

	public function checkResourceAlreadyAssignedToOtherService($resource_id, $service_id, $day) {
		// check if resource is already assigned to an other service at the same time
		$service = $this->dbhandle->getService($service_id);
		if($service == null) return false;
		$timeStart = strtotime($day.' '.$service->start);
		$timeEnd = strtotime($day.' '.$service->end);
		foreach($this->dbhandle->getPlannedServiceResourcesByResourceAndDay($resource_id, $day) as $psr) {
			$time2Start = strtotime($day.' '.$psr->service_start);
			$time2End = strtotime($day.' '.$psr->service_end);
			if(($timeStart == $time2Start)
			|| ($time2Start < $timeStart && $timeStart < $time2End)
			|| ($timeStart < $time2Start && $time2Start < $timeEnd)) {
				#echo $psr->service_id;
				return false;
			}
		}
		return true;
	}

}
