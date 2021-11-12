<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

$roster = null;

if(isset($_GET['roster'])) {
	$roster = $db->getRoster($_GET['roster']);
} else {
	die('<div class="infobox red">'.LANG['roster_not_found'].'</div>');
}

if(!empty($_POST['roster']) && !empty($_POST['users'])) {
	$success = null;

	$db->beginTransaction();
	$success = $db->removeUserToRosterByRoster($_POST['roster']);
	foreach($_POST['users'] as $uid) {
		if($success == null || $success == true)
			$success = $db->insertUserToRoster($uid, $_POST['roster']);
	}

	if($success) {
		$db->commitTransaction();
		header('Location: index.php?view=rosters');
		die();
	} else {
		$db->rollbackTransaction();
		$info = LANG['employee_could_not_be_assigned'].' '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}
?>
<div class="contentbox small">
	<h2><?php echo LANG['assign_employee']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
		<table>
			<tr>
				<th><?php echo LANG['roster']; ?>:</th>
				<td>
					<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
					<input type="text" disabled="true" value="<?php echo htmlspecialchars($roster->title); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php echo LANG['employees']; ?>:
					<div class="hint" style="width: 120px;">
						<?php echo LANG['ctrl_to_select_multiple']; ?>
					</div>
				</th>
				<td>
					<select name="users[]" multiple="true" size="25">
						<?php
						foreach($db->getUsers() as $u) {
							$selected = '';
							foreach($db->getUserRosters($u->id) as $r) {
								if($roster->id == $r->roster_id) {
									$selected = 'selected="true"';
									break;
								}
							}
							echo "<option value=\"".$u->id."\" $selected>".htmlspecialchars($u->fullname)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
			<tr>
				<td></td>
				<td><button type="reset"><img src='img/refresh.svg'>&nbsp;<?php echo LANG['reset']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
