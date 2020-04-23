<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

$services = [];
$preselectRoster = -1;
foreach(explode(',',$_GET['services']) as $service_id) {
	$service = $db->getService($service_id);
	if($service != null) {
		$services[] = $service;
		$preselectRoster = $service->roster_id;
	}
}
if(count($services) == 0)
	die('<div class="infobox red">Keine Dienste ausgewählt</div>');

if(!empty($_POST['roster'])) {
	$success = null;

	$db->beginTransaction();
	foreach($services as $s) {
		if($success == null || $success == true)
		$success = $db->createService(
			$_POST['roster'], $s->shortname.$_POST['suffix'], $s->title.$_POST['suffix'], $s->location, $s->employees, $s->start, $s->end,
			$_POST['date_start'], $_POST['date_end'], $s->color, $s->wd1, $s->wd2, $s->wd3, $s->wd4, $s->wd5, $s->wd6, $s->wd7
		);
	}

	if($success) {
		$db->commitTransaction();
		header('Location: index.php?view=rosters');
		die();
	} else {
		$db->rollbackTransaction();
		$info = 'Dienste konnten nicht kopiert werden: '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}
?>
<div class="contentbox small">
	<h2>Dienste kopieren</h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
		<table>
			<tr>
				<th>Ziel-Dienstplan:</th>
				<td>
					<select name="roster">
						<?php foreach($db->getRosters() as $r) {
							echo "<option ".htmlinput::selectIf($r->id,$preselectRoster).">".htmlspecialchars($r->title)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Ausgewählte Dienste:</th>
				<td>
					<input type="text" disabled="true" value="<?php echo count($services); ?>">
				</td>
			</tr>
			<tr>
				<th>Suffix anhängen:</th>
				<td><input type="text" name="suffix" value="2"></td>
			</tr>
			<tr>
				<th>Gültig ab:</th>
				<td><input type="date" name="date_start"></td>
			</tr>
			<tr>
				<th>Gültig bis:</th>
				<td><input type="date" name="date_end"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Kopieren</button></td>
			</tr>
		</table>
	</form>
</div>
