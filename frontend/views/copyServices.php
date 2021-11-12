<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
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
	die('<div class="infobox red">'.LANG['no_services_selected'].'</div>');

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
		$info = LANG['services_could_not_be_copied'].' '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}
?>
<div class="contentbox small">
	<h2><?php echo LANG['copy_services']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
		<table>
			<tr>
				<th><?php echo LANG['target_roster']; ?>:</th>
				<td>
					<select name="roster">
						<?php foreach($db->getRosters() as $r) {
							echo "<option ".htmlinput::selectIf($r->id,$preselectRoster).">".htmlspecialchars($r->title)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['selected_services']; ?>:</th>
				<td>
					<input type="text" disabled="true" value="<?php echo count($services); ?>">
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['append_suffix']; ?>:</th>
				<td><input type="text" name="suffix" value="2"></td>
			</tr>
			<tr>
				<th><?php echo LANG['valid_from']; ?>:</th>
				<td><input type="date" name="date_start"></td>
			</tr>
			<tr>
				<th><?php echo LANG['valid_to']; ?>:</th>
				<td><input type="date" name="date_end"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['copy_services']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
