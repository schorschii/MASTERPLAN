<?php
require_once('session.php');
require_once('../lib/loader.php');

$currentUser = $db->getUser($_SESSION['mp_userid']);


if(isset($_POST['action'])) {
	if($_POST['action'] == 'remove_assignment' && !empty($_POST['id'])) {

		$ps = $db->getPlannedService($_POST['id']);
		if(!$perm->isUserAdminForRoster($currentUser, $ps->service_roster_id)) {
			die('Sie besitzen keine Admin-Rechte für diesen Dienstplan');
		}

		$plan = new plan($db);
		if($plan->removeAssignment($ps->id)) {
			die('OK');
		} else {
			die('Dienstzuweisung konnte nicht entfernt werden: '.$db->getLastStatement()->error);
		}

	}
	elseif($_POST['action'] == 'remove_file' && !empty($_POST['id'])) {

		$plannedServiceFile = $db->getPlannedServiceFile($_POST['id']);
		if($plannedServiceFile != null) {
			$service = $db->getService($plannedServiceFile->service_id);
			if(!$perm->isUserAdminForRoster($currentUser, $service->roster_id)) {
				die('Sie besitzen keine Admin-Rechte für diesen Dienstplan');
			}

			if($db->removePlannedServiceFile($plannedServiceFile->id)) {
				die('OK');
			} else {
				die('Datei konnte nicht entfernt werden: '.$db->getLastStatement()->error);
			}
		}

	}
	elseif($_POST['action'] == 'remove_resource' && !empty($_POST['id'])) {

		$plannedServiceResource = $db->getPlannedServiceResource($_POST['id']);
		if($plannedServiceResource != null) {
			$service = $db->getService($plannedServiceResource->service_id);
			if(!$perm->isUserAdminForRoster($currentUser, $service->roster_id)) {
				die('Sie besitzen keine Admin-Rechte für diesen Dienstplan');
			}

			if($db->removePlannedServiceResource($plannedServiceResource->id)) {
				die('OK');
			} else {
				die('Ressource konnte nicht entfernt werden: '.$db->getLastStatement()->error);
			}
		}

	}
	elseif($_POST['action'] == 'set_scroll' && !empty($_POST['scroll'])) {

		$_SESSION['scroll'] = $_POST['scroll'];

	}
}
