<?php
require_once('session.php');
require_once('../lib/loader.php');
$currentUser = $db->getUser($_SESSION['mp_userid']);

if($_GET['type'] == 'servicefile') {
	$plannedServiceFile = $db->getPlannedServiceFile($_GET['id']);
	if($plannedServiceFile != null) {
		$service = $db->getService($plannedServiceFile->service_id);
		if($perm->isUserAssignedToRoster($currentUser, $service->roster_id)
		|| $perm->isUserAdminForRoster($currentUser, $service->roster_id)) {
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary");
			header("Content-disposition: attachment; filename=\"" . basename($plannedServiceFile->title) . "\"");
			echo $plannedServiceFile->file;
		}
	}
}
