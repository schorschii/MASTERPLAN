<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
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
		$info = LANG['database_cleaned'];
		$infoclass = 'green';
	} elseif($success === null) {
		$info = LANG['no_action_selected'];
		$infoclass = 'yellow';
	} else {
		$info = LANG['error'].': '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}
?>
<div class="contentbox small">
	<h2><?php echo LANG['database_cleanup']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<div class="infobox gray">
		<?php echo LANG['database_cleanup_description']; ?>
	</div>
	<form method="POST" onsubmit="return confirm('Möchten Sie die ausgewählten Aktionen jetzt ausführen?')">
		<label>
			<?php echo LANG['clear_data_including']; ?>:
			<input type="date" name="date">
		</label>
		<br><br>
		<label>
			<input type="checkbox" name="clear_old_assignments" value="1">
			<?php echo LANG['clear_user_service_assignments']; ?>
		</label>
		<br>
		<label>
			<input type="checkbox" name="clear_old_absences" value="1">
			<?php echo LANG['clear_absences']; ?>
		</label>
		<br>
		<label>
			<input type="checkbox" name="clear_old_swaps" value="1">
			<?php echo LANG['clear_swap_requests']; ?>
		</label>
		<br>
		<label>
			<input type="checkbox" name="clear_old_services" value="1">
			<?php echo LANG['clear_expired_services']; ?>
		</label>
		<div class="margintop">
			<button class="fullwidth"><img src="img/ok.svg">&nbsp;<?php echo LANG['start']; ?></button>
		</div>
	</form>
</div>
