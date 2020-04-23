<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

// cleanup selected items
if(isset($_POST['date'])) {
	$success = null;
	if($success !== false && isset($_POST['clear_old_assignments']) && $_POST['clear_old_assignments'] == 1) {
		$success = $db->removePlannedServicesOlderThan($_POST['date']);
	}
	if($success !== false && isset($_POST['clear_old_absences']) && $_POST['clear_old_absences'] == 1) {
		$success = $db->removeAbsencesOlderThan($_POST['date']);
	}
	if($success !== false && isset($_POST['clear_old_swaps']) && $_POST['clear_old_swaps'] == 1) {
		$success = $db->removeSwapServicesOlderThan($_POST['date']);
	}
	if($success !== false && isset($_POST['clear_old_services']) && $_POST['clear_old_services'] == 1) {
		$success = $db->removeExpiredServices();
	}
	if($success === true) {
		$info = 'Datenbank wurde bereinigt';
		$infoclass = 'green';
	} elseif($success === null) {
		$info = 'Es wurde keine Aktion ausgewählt';
		$infoclass = 'yellow';
	} else {
		$info = 'Fehler bei der Datenbank-Bereinigung: '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}
?>
<div class="contentbox small">
	<h2>Datenbank-Bereinigung</h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<div class="infobox gray">
		Mit diesem Tool können Sie nicht mehr benötigte Datensätze aus der Datenbank entfernen um Speicherplatz zu sparen und die Performance zu verbessern.
	</div>
	<form method="POST" onsubmit="return confirm('Möchten Sie die ausgewählten Aktionen jetzt ausführen?')">
		<label>
			Lösche Daten ab einschließlich:
			<input type="date" name="date">
		</label>
		<br><br>
		<label>
			<input type="checkbox" name="clear_old_assignments" value="1">
			Benutzer-Dienstzuweisungen, älter als oben angegeben, löschen
		</label>
		<br>
		<label>
			<input type="checkbox" name="clear_old_absences" value="1">
			Eingetragene Abwesenheiten, älter als oben angegeben, löschen
		</label>
		<br>
		<label>
			<input type="checkbox" name="clear_old_swaps" value="1">
			Tauschgesuche, älter als oben angegeben, löschen
		</label>
		<br>
		<label>
			<input type="checkbox" name="clear_old_services" value="1">
			Abgelaufene Dienste löschen
		</label>
		<div class="margintop">
			<button class="fullwidth"><img src="img/ok.svg">&nbsp;Start</button>
		</div>
	</form>
</div>
