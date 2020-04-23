<?php

require_once('../lib/loader.php');
$api = new api($db);
$api->checkActive();
$api->checkAuth();

// parse parameter
if(isset($_GET['view']) && isset($_GET['roster']) && isset($_GET['week'])) {
	$VIEW = $_GET['view'];
	$ROSTER = $_GET['roster'];
	$WEEK = $_GET['week'];
}
if(isset($_POST['view']) && isset($_POST['roster']) && isset($_POST['week'])) {
	$VIEW = $_POST['view'];
	$ROSTER = $_POST['roster'];
	$WEEK = $_POST['week'];
}
if(PHP_SAPI === 'cli') {
	foreach($argv as $arg) {
		if(tools::startsWith($arg, 'view='))
			$VIEW = explode('=', $arg)[1];
		if(tools::startsWith($arg, 'roster='))
			$ROSTER = explode('=', $arg)[1];
		if(tools::startsWith($arg, 'week='))
			$WEEK = explode('=', $arg)[1];
	}
}

// output
if($VIEW && $ROSTER && $WEEK) {

	if($VIEW == 'plan') {

		$html = new genhtml($db);
		$html->createPlanHtml($ROSTER, $WEEK);
		echo $html->getHtmlText();

	}
	elseif($VIEW == 'userServices') {

		$html = new genhtml($db);
		$html->createUserServicesHtml($ROSTER, $WEEK);
		echo $html->getHtmlText();

	}
	elseif($VIEW == 'freeUsers') {

		$html = new genhtml($db);
		$html->createFreeUsersHtml($ROSTER, $WEEK);
		echo $html->getHtmlText();

	}

} else {
	die('invalid api call');
}
