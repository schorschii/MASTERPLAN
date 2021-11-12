<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

// create/update
if(!empty($_POST['title'])) {
	$success = false;

	$success = $db->updateHoliday(
		isset($_POST['id']) ? $_POST['id'] : null,
		$_POST['title'], $_POST['day'],
		empty($_POST['service']) ? null : $_POST['service']
	);

	if($success) {
		if(isset($_POST['id'])) {
			header('Location: index.php?view=holidays');
			die();
		} else {
			$info = LANG['holiday_saved'];
			$infoclass = "green";
		}
	} else {
		$info = LANG['error'].': '.$db->getLastStatement()->error;
		$infoclass = "red";
	}
}


// display
$prefillTitle = '';
$prefillDay = '';
$prefillService = -1;

$h = null;
if(isset($_GET['id'])) {
	$h = $db->getHoliday($_GET['id']);
	if($h == null) die('<div class="infobox red">'.LANG['not_found'].'</div>');
	$prefillTitle = $h->title;
	$prefillDay = $h->day;
	$prefillService = $h->service_id;
}
?>

<div class='contentbox small'>
	<?php if($h == null) { ?>
		<h2><?php echo LANG['new_holiday']; ?></h2>
	<?php } else { ?>
		<h2><?php echo LANG['edit_holiday']; ?></h2>
	<?php } ?>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>

	<form method="POST" id="frmService">
		<?php if($h != null) { ?>
			<input type="hidden" name="id" value="<?php echo $h->id; ?>">
		<?php } ?>

		<table>
			<tr>
				<th><?php echo LANG['description']; ?>:</th>
				<td><input type="text" name="title" autofocus="true" value="<?php echo htmlspecialchars($prefillTitle); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['day']; ?>:</th>
				<td><input type="date" name="day" value="<?php echo htmlspecialchars($prefillDay); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['services']; ?>:</th>
				<td>
					<select name="service">
						<option value=''><?php echo LANG['all']; ?></option>
						<?php
						foreach($db->getRosters() as $r) {
							echo "<optgroup label='".htmlspecialchars($r->title)."'>";
							foreach($db->getServicesFromRoster($r->id) as $s) {
								$selected = '';
								if($s->id == $prefillService)
									$selected = 'selected="true"';
								echo "<option value='".$s->id."' ".$selected.">".htmlspecialchars($s->shortname." ".$s->title)."</option>";
							}
							echo "</optgroup>";
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
		</table>
	</form>
	<p>
		<a href="?view=holidays" class="button fullwidth"><img src='img/holiday.svg'>&nbsp;<?php echo LANG['show_existing_holidays']; ?></a>
	</p>
</div>
