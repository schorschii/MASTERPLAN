<?php
require_once('session.php');
require_once('../lib/loader.php');


// query current user for usage in subviews
$currentUser = $db->getUser($_SESSION['mp_userid']);

if(isset($_GET['export']) && isset($_GET['type'])) {
	if($_GET['export'] == 'plan' && !empty($_GET['roster']) && !empty($_GET['week'])) {
		// check user rights
		if($perm->isUserAdminForRoster($currentUser, $_GET['roster'])
		|| $perm->isUserAssignedToRoster($currentUser, $_GET['roster'])) {
			if($_GET['type'] == 'pdf') {
				$pdf = new genpdf($db);
				$pdf->createPlanPdf($_GET['roster'], $_GET['week']);
				$pdf->getPdfHandle()->Output('I', 'MASTERPLAN');
				die();
			}
		} else {
			die('Sie besitzen keine Leserechte für den angeforderten Dienstplan');
		}
	}
	elseif($_GET['export'] == 'userServices' && !empty($_GET['roster']) && !empty($_GET['week'])) {
		// check user rights
		if($perm->isUserAdminForRoster($currentUser, $_GET['roster'])) {
			if($_GET['type'] == 'pdf') {
				$pdf = new genpdf($db);
				$pdf->createUserServicesPdf($_GET['roster'], $_GET['week']);
				$pdf->getPdfHandle()->Output('I', 'MASTERPLAN');
				die();
			}
		} else {
			die('Sie besitzen keine Leserechte für den angeforderten Dienstplan');
		}
	}
	elseif($_GET['export'] == 'freeUsers' && !empty($_GET['roster']) && !empty($_GET['week'])) {
		// check user rights
		if($perm->isUserAdminForRoster($currentUser, $_GET['roster'])) {
			if($_GET['type'] == 'pdf') {
				$pdf = new genpdf($db);
				$pdf->createFreeUsersPdf($_GET['roster'], $_GET['week']);
				$pdf->getPdfHandle()->Output('I', 'MASTERPLAN');
				die();
			}
		} else {
			die('Sie besitzen keine Leserechte für den angeforderten Dienstplan');
		}
	}
	elseif($_GET['export'] == 'absence' && !empty($_GET['user'])) {
		// check user rights
		if($perm->isUserSuperadmin($currentUser) || $currentUser->id == $_GET['user']) {
			if($_GET['type'] == 'pdf') {
				$pdf = new genpdf($db);
				$pdf->createAbsencePdf($_GET['user']);
				$pdf->getPdfHandle()->Output('I', 'MASTERPLAN');
				die();
			}
		} else {
			die('Sie besitzen keine Leserechte');
		}
	}
}
