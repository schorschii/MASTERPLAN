<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

if(isset($_POST['action'])) {
	if($_POST['action'] == 'absence' && !empty($_POST['start']) && !empty($_POST['end'])) {

		$userid = $_POST['user'];
		if(isAllowedForUser($userid)) {

			if(strtotime($_POST['start']) <= strtotime($_POST['end'])) {

				if(abs(strtotime($_POST['start']) - strtotime($_POST['end'])) < 60*60*24*60) {

					$result = $db->updateAbsence(null, $userid, $_POST['type'], $_POST['start'], $_POST['end'], $_POST['comment'], 1);
					if($result) {
						$db->approveAbsence1($result, 1 , $currentUser->id);
						$db->approveAbsence2($result, 1 , $currentUser->id);

						// check if user is assigned to a service in this time range
						$plan = new plan($db);
						$error = false;
						$hasServices = 0;
						for($i = strtotime($_POST['start']); $i <= strtotime($_POST['end']); $i = $i + 86400) {
							$services = $db->getPlannedServicesByUserAndDay($userid, date('Y-m-d', $i));
							foreach($services as $ps) {
								if(!$plan->removeAssignment($ps->id)) {
									$error = true;
									break;
								}
							}
							$hasServices += count($services);
						}

						if($error) {
							$info = LANG['absence_saved_services_could_not_be_marked_as_vacant'];
							$infoclass = 'red';
						} else {
							$info = str_replace('%1', $hasServices, LANG['absence_saved_services_are_now_vacant']);
							$infoclass = 'green';
						}

					} else {
						$info = LANG['absence_could_not_be_saved'].' '.$db->getLastStatement()->error;
						$infoclass = 'red';
					}

				} else {
					$info = LANG['absence_too_long'];
					$infoclass = 'red';
				}

			} else {
				$info = LANG['end_date_before_start_date'];
				$infoclass = 'red';
			}

		}

	}
	elseif($_POST['action'] == 'removeAbsence') {
		if($db->removeAbsence($_POST['id'])) {
			$info = LANG['absence_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['absence_could_not_be_removed'].' '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
}

function isAllowedForUser($user_id) {
	global $db;
	global $perm;
	global $currentUser;
	if($currentUser->superadmin > 0) return true;
	foreach($db->getUserRosters($user_id) as $ur) {
		if($perm->isUserAdminForRoster($currentUser, $ur->roster_id))
			return true;
	}
	return false;
}
?>

<div class="contentbox small">
	<h2><?php echo LANG['enter_short_absence']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<p>
		<?php echo LANG['enter_short_absence_description']; ?>
	</p>
	<p>
		<?php echo LANG['enter_short_absence_description2']; ?>
	</p>
	<form method="POST" onsubmit="return confirm('<?php echo LANG['short_absence_assigned_service_note']; ?>')">
		<input type="hidden" id="action" name="action" value="absence">
		<table>
			<tr>
				<th><?php echo LANG['employee']; ?>:</th>
				<td>
					<select name="user" autofocus="true">
						<?php
						foreach($db->getUsers() as $u) {
							if(!isAllowedForUser($u->id)) continue;
						?>
							<option <?php echo htmlinput::selectIf($u->id,null); ?>><?php echo trim(htmlspecialchars($u->fullname)." (".htmlspecialchars($u->login).")"); ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['type']; ?>:</th>
				<td>
					<select name="type">
						<?php foreach($db->getAbsentTypes() as $at) {
							echo "<option value='".$at->id."'>".htmlspecialchars($at->shortname)." ".htmlspecialchars($at->title)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['begin']; ?>:</th>
				<td><input type="date" name="start" value=""></td>
			</tr>
			<tr>
				<th><?php echo LANG['end']; ?>:</th>
				<td><input type="date" name="end" value=""></td>
			</tr>
			<tr>
				<th><?php echo LANG['comment']; ?>:</th>
				<td><input type="text" name="comment" value="" placeholder="(optional)"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
			<tr>
				<th></th>
				<td><a class="button fullwidth" href="?view=absence"><img src="img/absent.svg">&nbsp;<?php echo LANG['jump_to_normal_absence_module']; ?></a></td>
			</tr>
		</table>
	</form>
</div>
