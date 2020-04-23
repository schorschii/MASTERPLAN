<?php
require_once('session.php');
require_once('../lib/loader.php');


// query current user for usage in subviews
$currentUser = $db->getUser($_SESSION['mp_userid']);

// is current user admin for at least one roster?
$showRosterAdminControls = false;
$adminRosters = $db->getUserRostersAdmin($_SESSION['mp_userid']);
if($currentUser->superadmin > 0 || count($adminRosters) > 0)
	$showRosterAdminControls = true;

// decide if menu bar should be displayed
$menuBarVisible = true;
if(isset($_GET['nomenubar']) && $_GET['nomenubar'] == 1)
	$menuBarVisible = false;

// decide which view to serve
$view = 'start';
if(isset($_GET['view'])) {
	switch($_GET['view']) {
		case 'about':
		case 'absence':
		case 'absenceLastMinute':
		case 'absenceApprove':
		case 'assignUsersToRoster':
		case 'changePassword':
		case 'copyServices':
		case 'dbMaintenance':
		case 'deployUser':
		case 'deployNote':
		case 'deployFile':
		case 'deployResource':
		case 'editAbsentTypes':
		case 'editHoliday':
		case 'editResource':
		case 'editRole':
		case 'editRoster':
		case 'editService':
		case 'editUser':
		case 'editUserBulk':
		case 'editUserConstraint':
		case 'editTextTemplates':
		case 'freeUsers':
		case 'holidays':
		case 'ownServices':
		case 'plan':
		case 'resources':
		case 'roles':
		case 'rosters':
		case 'settings':
		case 'start':
		case 'stat':
		case 'userBirthdays':
		case 'users':
		case 'userAnniversaries':
		case 'userServices':
		case 'vacantServices':
		case 'viewIcsUrl':
			$view = $_GET['view'];
			break;
	}
}

ob_start();
?>

<!DOCTYPE html>
<html>
	<head>
		<title>MASTERPLAN</title>
		<?php require('head.inc.php'); ?>
		<?php if(file_exists(TMP_FILES.'/'.'bg.image')) { ?>
			<style>
			html, body {
				background-image: url("../tmp/bg.image");
			}
			</style>
		<?php } ?>
	</head>
	<body>
		<?php if($view == 'start' && rand(10,30) == 25) { ?>
			<link href="css/tux.css" rel="stylesheet"></link>
			<img src="img/tux.svg" id="tux">
		<?php } ?>

		<?php if($menuBarVisible) { ?>
		<div id='topmenu'>
			<ul>
				<li class='logo'><a href='?view=start'><img src='img/favicon.png'></a></li>
				<?php if($currentUser != null && $currentUser->superadmin > 0) { ?>
				<li><a href='#'>Stammdaten</a>
					<ul>
						<li><a href='?view=rosters'><img src='img/roster.svg'>&nbsp;Dienstpläne und Dienste</a></li>
						<li><a href='?view=users'><img src='img/users.svg'>&nbsp;Benutzerverwaltung</a></li>
						<li><a href='?view=roles'><img src='img/roles.svg'>&nbsp;Rollenverwaltung</a></li>
						<li><a href='?view=resources'><img src='img/resources.svg'>&nbsp;Ressourcenverwaltung</a></li>
						<hr>
						<li><a href='?view=settings'><img src='img/settings.svg'>&nbsp;Globale Einstellungen</a></li>
						<li><a href='?view=dbMaintenance'><img src='img/clean.svg'>&nbsp;Datenbankbereinigung</a></li>
					</ul>
				</li>
				<?php } ?>
				<li><a href='#'>Planung</a>
					<ul>
						<li><a href='?view=plan'><img src='img/roster.svg'>&nbsp;Planungs-Ansicht</a></li>
						<?php if($showRosterAdminControls) { ?>
						<hr>
						<li><a href='?view=userServices'><img src='img/grid.svg'>&nbsp;Mitarbeiter-Einsatzplan</a></li>
						<li><a href='?view=freeUsers'><img src='img/free.svg'>&nbsp;Freie Mitarbeiter</a></li>
						<li><a href='?view=stat'><img src='img/chart.svg'>&nbsp;Auswertung / Statistik</a></li>
						<hr>
						<li><a href='?view=absenceApprove'><img src='img/absent-approve.svg'>&nbsp;Abwesenheiten freigeben</a></li>
						<li><a href='?view=absenceLastMinute'><img src='img/absent-last-minute.svg'>&nbsp;Kurzfristige Abwesenheit</a></li>
						<?php } ?>
					</ul>
				</li>
				<li><a href='#'>Self Care</a>
					<ul>
						<li><a href='?view=ownServices'><img src='img/checklist.svg'>&nbsp;Meine Dienste &amp; Diensttausch</a></li>
						<li><a href='?view=viewIcsUrl'><img src='img/calendar.svg'>&nbsp;Dienste als Kalender einbinden</a></li>
						<li><a href='?view=vacantServices'><img src='img/swap.svg'>&nbsp;Vakante Dienste & Tauschgesuche</a></li>
						<li><a href='?view=absence'><img src='img/absent.svg'>&nbsp;Abwesenheit eintragen</a></li>
						<?php if($currentUser->ldap == 0) { ?>
						<hr>
						<li><a href='?view=changePassword'><img src='img/key.svg'>&nbsp;Kennwort ändern</a></li>
						<?php } ?>
					</ul>
				</li>
				<li><a href='#'>Hilfe</a>
					<ul>
						<li><a href='?view=about'><img src='img/info.svg'>&nbsp;Informationen</a></li>
						<li><a href='manual/de.pdf' target='_blank'><img src='img/book.svg'>&nbsp;Handbuch</a></li>
					</ul>
				</li>
				<li class='right'>
					<a class='disabled'>
						<?php
						$displayName = 'Anonymous';
						if($currentUser != null) {
							$displayName = $currentUser->fullname;
							if(isset(USERNAME_OVERRIDES[$currentUser->login])) {
								$displayName = USERNAME_OVERRIDES[$currentUser->login];
							}
						}
						echo htmlspecialchars($displayName);
						?>
					</a>
					<a href='login.php?logout=1'>Abmelden</a>
				</li>
			</ul>
		</div>
		<?php } ?>

		<div id='content'>
			<?php if(!$lic->licenseValid) { ?>
				<div class='infobox yellow'><?php echo $lic->licenseText; ?></div>
			<?php } ?>
			<?php
			// rights check
			if($currentUser == null) {
				die('<div class="infobox red">Ihr Benutzerkonto existiert nicht mehr in der Datenbank</div>');
			} else {
				$file = 'views/'.$view.'.php';
				if(file_exists($file) && is_file($file))
					require($file);
				else echo ':-O';
			}
			?>
		</div>

		<div id='footer'>
		</div>

		<?php require_once('board.inc.php'); ?>
	</body>
</html>

<?php
ob_end_flush();
flush();
?>
