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
	die('<div class="infobox red">'.LANG['service_not_found'].'</div>');
}

$notes = $db->getPlannedServiceNotes($service->id, $day);

if(isset($_POST['service']) && isset($_POST['day']) && isset($_POST['note'])) {
	// check user rights
	if($perm->isUserAdminForRoster($currentUser, $service->roster_id)) {

		if($db->updatePlannedServiceNote($_POST['service'], $_POST['day'], $_POST['note'])) {
			echo "<script>self.close()</script>";
			die();
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}

	} else {
		$info = LANG['no_admin_rights_for_this_roster'];
		$infoclass = 'yellow';
	}
}
?>
<div class="contentbox small">
	<h2><?php echo LANG['add_note']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
		<table>
			<tr>
				<th><?php echo LANG['day']; ?>:</th>
				<td>
					<input type="hidden" name="day" value="<?php echo htmlspecialchars($day); ?>">
					<input type="text" disabled="true" value="<?php echo htmlspecialchars( strftime(DATE_FORMAT, strtotime($day)) ); ?>">
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['service']; ?>:</th>
				<td>
					<input type="hidden" name="service" value="<?php echo $service->id; ?>">
					<input type="text" disabled="true" value="<?php echo htmlspecialchars($service->shortname)." ".htmlspecialchars($service->title); ?>">
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['note']; ?>:</th>
				<td>
					<textarea name="note" autofocus="true" rows="10"><?php if(isset($notes[0])) echo $notes[0]->note; ?></textarea>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
