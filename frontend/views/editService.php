<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
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
			$info = LANG['service_saved'];
			$infoclass = 'green';
		}
	} else {
		$info = LANG['error'].': '.$db->getLastStatement()->error;
		$infoclass = 'red';
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
	if($s == null) die('<div class="infobox red">'.LANG['not_found'].'</div>');
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
		<h2><?php echo LANG['new_service']; ?></h2>
	<?php } else { ?>
		<h2><?php echo LANG['edit_service']; ?></h2>
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
				<th><?php echo LANG['roster']; ?>:</th>
				<td>
					<select name="roster">
						<?php foreach($db->getRosters() as $r) {
							echo "<option ".htmlinput::selectIf($r->id,$preselectRoster).">".htmlspecialchars($r->title)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['short_name']; ?>:</th>
				<td><input type="text" name="shortname" maxlength="10" autofocus="true" value="<?php echo htmlspecialchars($prefillShortname); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['title']; ?>:</th>
				<td><input type="text" name="title" placeholder="(<?php echo LANG['optional']; ?>)" value="<?php echo htmlspecialchars($prefillTitle); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['location']; ?>:</th>
				<td><input type="text" name="location" placeholder="(<?php echo LANG['optional']; ?>)" value="<?php echo htmlspecialchars($prefillLocation); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['number_of_employees']; ?>:</th>
				<td><input type="number" name="employees" value="<?php echo htmlspecialchars($prefillEmployees); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['begin']; ?>:</th>
				<td><input type="time" name="start" value="<?php echo htmlspecialchars($prefillStart); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['end']; ?>:</th>
				<td><input type="time" name="end" value="<?php echo htmlspecialchars($prefillEnd); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['valid_from']; ?>:</th>
				<td><input type="date" name="date_start" value="<?php echo htmlspecialchars($prefillDateStart); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['valid_to']; ?>:</th>
				<td><input type="date" name="date_end" value="<?php echo htmlspecialchars($prefillDateEnd); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['color']; ?>:</th>
				<td><input type="color" name="color" value="<?php echo htmlspecialchars($prefillColor); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['weekdays']; ?>:</th>
				<td>
					<div>
						<input type="hidden" name="wd1" value="0">
						<label><input type="checkbox" name="wd1" <?php echo htmlinput::check(1,$preselectWd1); ?>><?php echo LANG['monday']; ?></label>
					</div>
					<div>
						<input type="hidden" name="wd2" value="0">
						<label><input type="checkbox" name="wd2" <?php echo htmlinput::check(1,$preselectWd2); ?>><?php echo LANG['tuesday']; ?></label>
					</div>
					<div>
						<input type="hidden" name="wd3" value="0">
						<label><input type="checkbox" name="wd3" <?php echo htmlinput::check(1,$preselectWd3); ?>><?php echo LANG['wednesday']; ?></label>
					</div>
					<div>
						<input type="hidden" name="wd4" value="0">
						<label><input type="checkbox" name="wd4" <?php echo htmlinput::check(1,$preselectWd4); ?>><?php echo LANG['thursday']; ?></label>
					</div>
					<div>
						<input type="hidden" name="wd5" value="0">
						<label><input type="checkbox" name="wd5" <?php echo htmlinput::check(1,$preselectWd5); ?>><?php echo LANG['friday']; ?></label>
					</div>
					<div>
						<input type="hidden" name="wd6" value="0">
						<label><input type="checkbox" name="wd6" <?php echo htmlinput::check(1,$preselectWd6); ?>><?php echo LANG['saturday']; ?></label>
					</div>
					<div>
						<input type="hidden" name="wd7" value="0">
						<label><input type="checkbox" name="wd7" <?php echo htmlinput::check(1,$preselectWd7); ?>><?php echo LANG['sunday']; ?></label>
					</div>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
