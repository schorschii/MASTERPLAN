<?php

require_once('../lib/loader.php');
$api = new api($db);
$api->checkActive();

if(!empty($_GET['user'])
&& !empty($_GET['auth'])
&& !empty($_GET['view'])) {

	$user = $db->getUser($_GET['user']);
	if(md5($user->password) != $_GET['auth']) die('authentication failed');

	$icsDomain = $db->getSetting('ics_domain', '');

	if($_GET['view'] == 'userServices') {

		// query planned services
		$allPlannedServices = $db->getPlannedServicesByUser($user->id);
		foreach($allPlannedServices as $ps) {
			// check if roster is released
			$released = false;
			foreach($db->getReleasedPlansByRoster($ps->service_roster_id) as $rp) {
				if($rp->day == $ps->day) {
					$released = true;
					break;
				}
			}
			if($released) {
				$icsRoster = $db->getRoster($ps->service_roster_id);
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
					false
				);
				echo $ics."\n\n";
			}
		}

	}
	elseif($_GET['view'] == 'roster' && !empty($_GET['roster'])) {

		// check permissions
		if(!
			($perm->isUserAdminForRoster($user, $_GET['roster'])
			|| $perm->isUserAssignedToRoster($user, $_GET['roster']))
		) {
			die('permission violation');
		}
		// query planned services
		$allPlannedServices = $db->getPlannedServicesWithUserByRoster($_GET['roster']);
		foreach($allPlannedServices as $ps) {
			// check if roster is released
			$released = false;
			foreach($db->getReleasedPlansByRoster($ps->service_roster_id) as $rp) {
				if($rp->day == $ps->day) {
					$released = true;
					break;
				}
			}
			if($released) {
				$icsRoster = $db->getRoster($ps->service_roster_id);
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
					false
				);
				echo $ics."\n\n";
			}
		}

	}
	else {
		die('invalid api call');
	}

}
