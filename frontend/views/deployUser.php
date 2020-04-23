<?php
$info = null;
$infoclass = null;
$force = false;

// rights check
if(!isset($currentUser)) die();

$service = null;
$day = null;
$force = 0;
$forceNext = 0;

if(isset($_POST['force'])) {
	$force = $_POST['force'];
}
if(isset($_POST['forceUser']) && isset($_POST['user'])) {
	if($_POST['forceUser'] != $_POST['user']) {
		$force = 0;
	}
}
if(isset($_GET['day'])) {
	$day = $_GET['day'];
}
if(isset($_GET['service'])) {
	$service = $db->getService($_GET['service']);
} else {
	die('<div class="infobox red">Dienst nicht gefunden</div>');
}

if(isset($_POST['service']) && isset($_POST['day']) && isset($_POST['user'])) {
	// check user rights
	if($perm->isUserAdminForRoster($currentUser, $service->roster_id)) {

		$roster = $db->getRoster($service->roster_id);

		$aplan = new autoplan($db);
		if($force >= 1 || $aplan->checkUserConstraintOrAbsent($_POST['user'], $_POST['service'], $_POST['day'])) {

			if($force >= 2 || $aplan->checkUserAlreadyAssignedToOtherService($_POST['user'], $_POST['service'], $_POST['day'])) {

				$strWeek = date('Y', strtotime($_POST['day'])).'-W'.date('W', strtotime($_POST['day']));
				if($force >= 3 || $roster->ignore_working_hours || $aplan->canUserHandleWorkHoursInWeek($_POST['user'], autoplan::getServiceWorkTime($service), $strWeek, date('Y-m-d', strtotime($_POST['day'])))) {

					if($force >= 4 || $aplan->checkUserRestTime($_POST['user'], $_POST['service'], $_POST['day'])) {

						if($db->updatePlannedService(null, $_POST['day'], $_POST['service'], $_POST['user'])) {
							#$week = date('Y', strtotime($_POST['day'])).'-W'.date('W', strtotime($_POST['day']));
							#header('Location: index.php?view=plan&roster='.$service->roster_id.'&week='.urlencode($week));
							echo "<script>self.close()</script>";
							die();
						} else {
							$info = 'Benutzer konnte nicht zugeordnet werden: '.$db->getLastStatement()->error;
							$infoclass = 'red';
						}

					} else {
						$info = 'Der Mitarbeiter "'.htmlspecialchars($db->getUser($_POST['user'])->fullname).'" kann nicht für diesen Dienst eingeteilt werden, da er am Tag zuvor für einen Spätdienst eingesetzt ist und die Ruhezeit verletzt werden würde. Führen Sie den Vorgang erneut aus, um die Einteilung zu erzwingen.';
						$infoclass = 'yellow';
						$forceNext = 4;
					}

				} else {
					$info = 'Der Mitarbeiter "'.htmlspecialchars($db->getUser($_POST['user'])->fullname).'" kann aufgrund aufgrund von Überlastung nicht für diesen Dienst eingeteilt werden. Führen Sie den Vorgang erneut aus, um die Einteilung zu erzwingen.';
					$infoclass = 'yellow';
					$forceNext = 3;
				}

			} else {
				$info = 'Der Mitarbeiter "'.htmlspecialchars($db->getUser($_POST['user'])->fullname).'" kann nicht für diesen Dienst eingeteilt werden, da er bereits zur gleichen Zeit für einen anderen Dienst eingetragen ist. Führen Sie den Vorgang erneut aus, um die Einteilung zu erzwingen.';
				$infoclass = 'yellow';
				$forceNext = 2;
			}

		} else {
			$info = 'Der Mitarbeiter "'.htmlspecialchars($db->getUser($_POST['user'])->fullname).'" kann aufgrund einer Beschränkung oder einer eingetragenen Abwesenheit nicht für diesen Dienst eingeteilt werden. Führen Sie den Vorgang erneut aus, um die Einteilung zu erzwingen.';
			$infoclass = 'yellow';
			$forceNext = 1;
		}

	} else {
		$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
		$infoclass = 'yellow';
	}
}
?>
<div class="contentbox small">
	<h2>Mitarbeiter zuweisen</h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
		<input type="hidden" name="force" value="<?php echo $forceNext; ?>">
		<?php if(isset($_POST['user'])) { ?>
			<input type="hidden" name="forceUser" value="<?php echo $_POST['user']; ?>">
		<?php } ?>
		<table>
			<tr>
				<th>Tag:</th>
				<td>
					<input type="hidden" name="day" value="<?php echo htmlspecialchars($day); ?>">
					<input type="text" disabled="true" value="<?php echo htmlspecialchars( strftime(DATE_FORMAT, strtotime($day)) ); ?>">
				</td>
			</tr>
			<tr>
				<th>Dienst:</th>
				<td>
					<input type="hidden" name="service" value="<?php echo $service->id; ?>">
					<input type="text" disabled="true" value="<?php echo htmlspecialchars($service->shortname)." ".htmlspecialchars($service->title); ?>">
				</td>
			</tr>
			<tr>
				<th>Mitarbeiter:</th>
				<td>
					<select name="user" autofocus="true">
						<?php
						$preselectUser = null;
						if(isset($_POST['user'])) $preselectUser = $_POST['user'];
						foreach($db->getUsersByRoster($service->roster_id) as $u) {
							echo "<option ".htmlinput::selectIf($u->id,$preselectUser).">".htmlspecialchars($u->fullname)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Zuweisen</button></td>
			</tr>
		</table>
	</form>
</div>
