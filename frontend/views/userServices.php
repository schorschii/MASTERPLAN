<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

$preselectRoster = null;
if(isset($_SESSION['last_roster'])) $preselectRoster = $_SESSION['last_roster'];
$preselectWeek = date('Y', strtotime('+1 week')).'-W'.date('W', strtotime('+1 week'));

$strWeek = null;
if(isset($_GET['week'])) {
	$strWeek = $_GET['week'];
	$preselectWeek = $strWeek;
}

$roster = null;
if(isset($_GET['roster'])) {
	// check user rights
	if($_GET['roster'] == -1) {
		if($perm->isUserSuperadmin($currentUser)) {
			$roster = -1;
			$preselectRoster = -1;
		} else {
			$info = 'Sie benötigen Superadmin-Rechte um den Einsatzplan aller Mitarbeiter einzusehen';
			$infoclass = 'yellow';
		}
	} else {
		if($perm->isUserAdminForRoster($currentUser, $_GET['roster'])) {
			$roster = $db->getRoster($_GET['roster']);
			$_SESSION['last_roster'] = $roster->id;
			if($roster != null) // null means all employees
				$preselectRoster = $roster->id;
		} else {
			$info = 'Sie besitzen keine Admin-Rechte für den angeforderten Dienstplan';
			$infoclass = 'yellow';
		}
	}
}

function echoUserServices($user_id, $day) {
	global $db;
	$absences = $db->getAbsencesByUser($user_id);
	foreach($absences as $a) {
		if(strtotime($a->start) <= strtotime($day)
		&& strtotime($a->end) >= strtotime($day)) {
			boxes::echoAbsence($a, $db);
		}
	}
	foreach($db->getPlannedServicesByUserAndDay($user_id, $day) as $ps) {
		boxes::echoService($db->getService($ps->service_id), $ps->day, $db, false);
	}
}
?>
<div class="contentbox">
	<form method="GET">
		<div class="inlineblock">
			Zeige Mitarbeiter aus Dienstplan:
			<select name="roster" autofocus="true">
				<?php if($currentUser->superadmin > 0) { ?>
					<option value='-1'>ALLE MITARBEITER</option>
				<?php } ?>
				<?php foreach($db->getRosters() as $r) {
					if(!$perm->isUserAdminForRoster($currentUser, $r->id)) continue;
					echo "<option ".htmlinput::selectIf($r->id,$preselectRoster).">".htmlspecialchars($r->title)."</option>";
				} ?>
			</select>
		</div>
		<div class="inlineblock">
			<input type="hidden" name="view" value="userServices">
			Woche:
			<input type="week" name="week" value="<?php echo htmlspecialchars($preselectWeek); ?>">
			<button><img src="img/refresh.svg">&nbsp;Anzeigen</button>
		</div>
	</form>
	<?php if($roster === null) { ?>
		<?php if($info != null) { ?>
			<div class="infobox margintop <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
		<?php } else { ?>
			<div class="infobox gray margintop">Bitte wählen Sie einen Einsatzplan aus</div>
		<?php } ?>
	<?php } ?>
</div>

<?php if($roster !== null) { ?>
<div class="contentbox">
	<h2><?php echo ($roster !== -1) ? 'Mitarbeiter '.htmlspecialchars($roster->title) : 'Alle Mitarbeiter'; ?>, <?php echo htmlspecialchars($strWeek); ?></h2>
	<div class="toolbar marginbottom">
		<form method="GET" class="inlineblock" action="export.php" target="_blank">
			<input type="hidden" name="export" value="userServices">
			<input type="hidden" name="type" value="pdf">
			<input type="hidden" name="roster" value="<?php echo $preselectRoster; ?>">
			<input type="hidden" name="week" value="<?php echo $strWeek; ?>">
			<button><img id="btnSendMailsImg" src="img/export.svg">&nbsp;PDF-Export</button>
		</form>
	</div>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<?php
	$timeWd1 = strtotime($strWeek.' +0 day');
	$timeWd2 = strtotime($strWeek.' +1 day');
	$timeWd3 = strtotime($strWeek.' +2 day');
	$timeWd4 = strtotime($strWeek.' +3 day');
	$timeWd5 = strtotime($strWeek.' +4 day');
	$timeWd6 = strtotime($strWeek.' +5 day');
	$timeWd7 = strtotime($strWeek.' +6 day');
	?>
	<table class="data plan timetable">
		<tr>
			<th>Mitarbeiter</th>
			<th><?php echo strftime('%A', $timeWd1); ?></th>
			<th><?php echo strftime('%A', $timeWd2); ?></th>
			<th><?php echo strftime('%A', $timeWd3); ?></th>
			<th><?php echo strftime('%A', $timeWd4); ?></th>
			<th><?php echo strftime('%A', $timeWd5); ?></th>
			<th><?php echo strftime('%A', $timeWd6); ?></th>
			<th><?php echo strftime('%A', $timeWd7); ?></th>
			<th>Auslastung</th>
		</tr>
		<tr class="topborder">
			<th></th>
			<th><?php echo strftime(DATE_FORMAT, $timeWd1); ?></th>
			<th><?php echo strftime(DATE_FORMAT, $timeWd2); ?></th>
			<th><?php echo strftime(DATE_FORMAT, $timeWd3); ?></th>
			<th><?php echo strftime(DATE_FORMAT, $timeWd4); ?></th>
			<th><?php echo strftime(DATE_FORMAT, $timeWd5); ?></th>
			<th><?php echo strftime(DATE_FORMAT, $timeWd6); ?></th>
			<th><?php echo strftime(DATE_FORMAT, $timeWd7); ?></th>
			<th></th>
		</tr>
		<?php
		$users = [];
		if($roster === -1) $users = $db->getUsers();
		else $users = $db->getUsersByRoster($roster->id);
		$aplan = new autoplan($db);
		foreach($users as $u) {
			// calc month hours
			$month = $aplan->getWorkByUserAndTimespan(
				$u->id,
				strtotime(date('Y-m-01', $timeWd1)),
				strtotime(date('Y-m-t', $timeWd1))
			);
			// calc week hours & services
			$week = $aplan->getWorkByUserAndTimespan($u->id, $timeWd1, $timeWd7);
		?>
			<tr class='topborder'>
				<th><?php boxes::echoUser($u); ?></th>
				<td>
					<?php echoUserServices($u->id, date('Y-m-d', $timeWd1)); ?>
				</td>
				<td>
					<?php echoUserServices($u->id, date('Y-m-d', $timeWd2)); ?>
				</td>
				<td>
					<?php echoUserServices($u->id, date('Y-m-d', $timeWd3)); ?>
				</td>
				<td>
					<?php echoUserServices($u->id, date('Y-m-d', $timeWd4)); ?>
				</td>
				<td>
					<?php echoUserServices($u->id, date('Y-m-d', $timeWd5)); ?>
				</td>
				<td>
					<?php echoUserServices($u->id, date('Y-m-d', $timeWd6)); ?>
				</td>
				<td>
					<?php echoUserServices($u->id, date('Y-m-d', $timeWd7)); ?>
				</td>
				<td>
					<?php if($u->max_services_per_week >= 0) { ?>
					<div class="hint2">
						<?php
						$servicesPercent = @round($week['services']*100/$u->max_services_per_week);
						echo '<b>'.$week['services'].'/'.$u->max_services_per_week.'</b> Dienste/Woche ('.$servicesPercent.'%)';
						?>
					</div>
					<?php } ?>
					<?php if($u->max_hours_per_week >= 0) { ?>
					<div class="hint2">
						<?php
						$weekHoursPercent = @round($week['hours']*100/$u->max_hours_per_week);
						echo '<b>'.$week['hours'].'/'.$u->max_hours_per_week.'</b> Std./Woche ('.$weekHoursPercent.'%)';
						?>
					</div>
					<?php } ?>
					<?php if($u->max_hours_per_month >= 0) { ?>
					<div class="hint2">
						<?php
						$monthHoursPercent = @round($month['hours']*100/$u->max_hours_per_month);
						echo '<b>'.$month['hours'].'/'.$u->max_hours_per_month.'</b> Std. '.strftime('%b',$timeWd1).' ('.$monthHoursPercent.'%)';
						?>
					</div>
					<?php } ?>
				</td>
		<?php } ?>
	</table>
</div>
<?php } ?>
