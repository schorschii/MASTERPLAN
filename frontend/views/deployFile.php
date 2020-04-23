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

if(isset($_POST['service']) && isset($_POST['day']) && isset($_FILES['file'])) {
	// check user rights
	if($perm->isUserAdminForRoster($currentUser, $service->roster_id)) {

		if($db->createPlannedServiceFile($_POST['service'], $_POST['day'], $_FILES['file']['name'], file_get_contents($_FILES['file']['tmp_name']))) {
			echo "<script>self.close()</script>";
			die();
		} else {
			$info = 'Datei konnte nicht hinzugefügt werden: '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}

	} else {
		$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
		$infoclass = 'yellow';
	}
}
?>
<div class="contentbox small">
	<h2>Datei hinzufügen</h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom" enctype="multipart/form-data">
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
				<th>Datei:</th>
				<td>
					<input type="file" name="file" autofocus="true">
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Hochladen</button></td>
			</tr>
		</table>
	</form>
</div>
