<?php
$info = null;
$infoclass = null;
$force = false;

// rights check
if(!isset($currentUser)) die();

$service = null;
$day = null;

if(isset($_GET['day'])) {
	$day = $_GET['day'];
}
if(isset($_GET['service'])) {
	$service = $db->getService($_GET['service']);
} else {
	die('<div class="infobox red">Dienst nicht gefunden</div>');
}

$notes = $db->getPlannedServiceNotes($service->id, $day);

if(isset($_POST['service']) && isset($_POST['day']) && isset($_POST['note'])) {
	// check user rights
	if($perm->isUserAdminForRoster($currentUser, $service->roster_id)) {

		if($db->updatePlannedServiceNote($_POST['service'], $_POST['day'], $_POST['note'])) {
			echo "<script>self.close()</script>";
			die();
		} else {
			$info = 'Notiz konnte nicht hinzugefügt werden: '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}

	} else {
		$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
		$infoclass = 'yellow';
	}
}
?>
<div class="contentbox small">
	<h2>Notiz hinzufügen</h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
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
				<th>Notiz:</th>
				<td>
					<textarea name="note" autofocus="true" rows="10"><?php if(isset($notes[0])) echo $notes[0]->note; ?></textarea>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Speichern</button></td>
			</tr>
		</table>
	</form>
</div>
