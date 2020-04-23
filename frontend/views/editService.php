<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

// create/update
if(!empty($_POST['shortname'])) {
	$success = false;
	if(isset($_POST['id'])) {
		$success = $db->updateService(
			$_POST['id'], $_POST['roster'], $_POST['shortname'], $_POST['title'], $_POST['location'], $_POST['employees'], $_POST['start'], $_POST['end'],
			$_POST['date_start'], $_POST['date_end'], $_POST['color'], $_POST['wd1'], $_POST['wd2'], $_POST['wd3'], $_POST['wd4'], $_POST['wd5'], $_POST['wd6'], $_POST['wd7']
		);
	} else {
		$success = $db->createService(
			$_POST['roster'], $_POST['shortname'], $_POST['title'], $_POST['location'], $_POST['employees'], $_POST['start'], $_POST['end'],
			$_POST['date_start'], $_POST['date_end'], $_POST['color'], $_POST['wd1'], $_POST['wd2'], $_POST['wd3'], $_POST['wd4'], $_POST['wd5'], $_POST['wd6'], $_POST['wd7']
		);
	}
	if($success) {
		if(isset($_POST['id'])) {
			header('Location: index.php?view=rosters');
			die();
		} else {
			$info = "Dienst gespeichert";
			$infoclass = "green";
		}
	} else {
		$info = "Dienst konnte nicht gespeichert werden: ".$db->getLastStatement()->error;
		$infoclass = "red";
	}
}


// display
$preselectRoster = '';
if(isset($_POST['roster'])) $preselectRoster = $_POST['roster'];
$prefillShortname = '';
$prefillTitle = '';
$prefillLocation = '';
$prefillStart = '09:00';
$prefillEnd = '15:30';
$prefillDateStart = date('Y-m-d');
$prefillDateEnd = date('Y-m-d', strtotime('+1 year'));
$prefillEmployees = 1;
$prefillColor = '#F5F5F5';
$preselectWd1 = 1;
$preselectWd2 = 1;
$preselectWd3 = 1;
$preselectWd4 = 1;
$preselectWd5 = 1;
$preselectWd6 = 0;
$preselectWd7 = 0;

$s = null;
if(isset($_GET['id'])) {
	$s = $db->getService($_GET['id']);
	if($s == null) die('<div class="infobox red">Dienst nicht gefunden</div>');
	$preselectRoster = $s->roster_id;
	$prefillShortname = $s->shortname;
	$prefillTitle = $s->title;
	$prefillLocation = $s->location;
	$prefillEmployees = $s->employees;
	$prefillStart = $s->start;
	$prefillEnd = $s->end;
	$prefillDateStart = $s->date_start;
	$prefillDateEnd = $s->date_end;
	$prefillColor = $s->color;
	$preselectWd1 = $s->wd1;
	$preselectWd2 = $s->wd2;
	$preselectWd3 = $s->wd3;
	$preselectWd4 = $s->wd4;
	$preselectWd5 = $s->wd5;
	$preselectWd6 = $s->wd6;
	$preselectWd7 = $s->wd7;
}
?>

<div class='contentbox small'>
	<?php if($s == null) { ?>
		<h2>Neuer Dienst</h2>
	<?php } else { ?>
		<h2>Dienst bearbeiten</h2>
	<?php } ?>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>

	<form method="POST" id="frmService">
		<?php if($s != null) { ?>
			<input type="hidden" name="id" value="<?php echo $s->id; ?>">
		<?php } ?>

		<table>
			<tr>
				<th>Dienstplan:</th>
				<td>
					<select name="roster">
						<?php foreach($db->getRosters() as $r) {
							echo "<option ".htmlinput::selectIf($r->id,$preselectRoster).">".htmlspecialchars($r->title)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Kürzel:</th>
				<td><input type="text" name="shortname" maxlength="10" autofocus="true" value="<?php echo htmlspecialchars($prefillShortname); ?>"></td>
			</tr>
			<tr>
				<th>Bezeichnung:</th>
				<td><input type="text" name="title" placeholder="(optional)" value="<?php echo htmlspecialchars($prefillTitle); ?>"></td>
			</tr>
			<tr>
				<th>Ort:</th>
				<td><input type="text" name="location" placeholder="(optional)" value="<?php echo htmlspecialchars($prefillLocation); ?>"></td>
			</tr>
			<tr>
				<th>Anzahl Mitarbeiter:</th>
				<td><input type="number" name="employees" value="<?php echo htmlspecialchars($prefillEmployees); ?>"></td>
			</tr>
			<tr>
				<th>Beginn:</th>
				<td><input type="time" name="start" value="<?php echo htmlspecialchars($prefillStart); ?>"></td>
			</tr>
			<tr>
				<th>Ende:</th>
				<td><input type="time" name="end" value="<?php echo htmlspecialchars($prefillEnd); ?>"></td>
			</tr>
			<tr>
				<th>Gültig ab:</th>
				<td><input type="date" name="date_start" value="<?php echo htmlspecialchars($prefillDateStart); ?>"></td>
			</tr>
			<tr>
				<th>Gültig bis:</th>
				<td><input type="date" name="date_end" value="<?php echo htmlspecialchars($prefillDateEnd); ?>"></td>
			</tr>
			<tr>
				<th>Farbe:</th>
				<td><input type="color" name="color" value="<?php echo htmlspecialchars($prefillColor); ?>"></td>
			</tr>
			<tr>
				<th>Wochentage:</th>
				<td>
					<div>
						<input type="hidden" name="wd1" value="0">
						<label><input type="checkbox" name="wd1" <?php echo htmlinput::check(1,$preselectWd1); ?>>Montag</label>
					</div>
					<div>
						<input type="hidden" name="wd2" value="0">
						<label><input type="checkbox" name="wd2" <?php echo htmlinput::check(1,$preselectWd2); ?>>Dienstag</label>
					</div>
					<div>
						<input type="hidden" name="wd3" value="0">
						<label><input type="checkbox" name="wd3" <?php echo htmlinput::check(1,$preselectWd3); ?>>Mittwoch</label>
					</div>
					<div>
						<input type="hidden" name="wd4" value="0">
						<label><input type="checkbox" name="wd4" <?php echo htmlinput::check(1,$preselectWd4); ?>>Donnerstag</label>
					</div>
					<div>
						<input type="hidden" name="wd5" value="0">
						<label><input type="checkbox" name="wd5" <?php echo htmlinput::check(1,$preselectWd5); ?>>Freitag</label>
					</div>
					<div>
						<input type="hidden" name="wd6" value="0">
						<label><input type="checkbox" name="wd6" <?php echo htmlinput::check(1,$preselectWd6); ?>>Samstag</label>
					</div>
					<div>
						<input type="hidden" name="wd7" value="0">
						<label><input type="checkbox" name="wd7" <?php echo htmlinput::check(1,$preselectWd7); ?>>Sonntag</label>
					</div>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Speichern</button></td>
			</tr>
		</table>
	</form>
</div>
