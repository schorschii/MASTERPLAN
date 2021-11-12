<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

if(!empty($_POST['title'])) {
		$error = false;

		$db->beginTransaction();

		// save role metadata
		$id = null;
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			if(!$db->updateRole(
				$id,
				$_POST['title'],
				$_POST['max_hours_per_day'],
				$_POST['max_services_per_week'],
				$_POST['max_hours_per_week'],
				$_POST['max_hours_per_month']
			)) $error = true;
		} else {
			if(!$id = $db->createRole(
				$_POST['title'],
				$_POST['max_hours_per_day'],
				$_POST['max_services_per_week'],
				$_POST['max_hours_per_week'],
				$_POST['max_hours_per_month']
			)) $error = true;
		}

		// save role user assignments
		if(!$error) {
			if($db->removeUserToRoleByRole($id)) {
				if(!empty($_POST['users'])) {
					foreach($_POST['users'] as $u_id) {
						if(!$db->insertUserToRole(
							$u_id, $id
						)) $error = true;
					}
				}
			} else $error = true;
		}

		// update user values based on role
		if(!$error) {
			$roles->updateRoleAffiliation();
		}

		$db->commitTransaction();

		if($error) {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		} else {
			if(isset($_POST['id'])) {
				header('Location: index.php?view=roles');
				die();
			} else {
				$info = LANG['role_saved'];
				$infoclass = 'green';
			}
		}
}

// display
$prefillTitle = '';
$prefillMaxHoursPerDay = '';
$prefillMaxServicesPerWeek = '';
$prefillMaxHoursPerWeek = '';
$prefillMaxHoursPerMonth = '';

$r = null;
if(isset($_GET['id'])) {
	$r = $db->getRole($_GET['id']);
	if($r == null) die('<div class="infobox red">Rolle nicht gefunden</div>');
	$prefillTitle = $r->title;
	$prefillMaxHoursPerDay = $r->max_hours_per_day;
	$prefillMaxServicesPerWeek = $r->max_services_per_week;
	$prefillMaxHoursPerWeek = $r->max_hours_per_week;
	$prefillMaxHoursPerMonth = $r->max_hours_per_month;
}
?>

<div class='contentbox small'>
	<h2><?php echo LANG['role_settings']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>

	<form method="POST">
		<?php if($r != null) { ?>
			<input type="hidden" name="id" value="<?php echo $r->id; ?>">
		<?php } ?>
		<table class="input">
			<tr>
				<th><?php echo LANG['title']; ?>:</th>
				<td><input type="text" name="title" autofocus="true" value="<?php echo htmlspecialchars($prefillTitle); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_hrs_day']; ?>:</th>
				<td><input type="number" name="max_hours_per_day" value="<?php echo $prefillMaxHoursPerDay; ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_services_week']; ?>:</th>
				<td><input type="number" name="max_services_per_week" value="<?php echo $prefillMaxServicesPerWeek; ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_hrs_week']; ?>:</th>
				<td><input type="number" name="max_hours_per_week" value="<?php echo $prefillMaxHoursPerWeek; ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_hrs_month']; ?>:</th>
				<td><input type="number" name="max_hours_per_month" value="<?php echo $prefillMaxHoursPerMonth; ?>"></td>
			</tr>
			<tr>
				<th>
					<?php echo LANG['assigned_users']; ?>:
					<div class="hint">
						<div><?php echo LANG['assigned_users_description']; ?></div>
					</div>
				</th>
				<td>
					<select multiple="true" class="fullwidth" name="users[]">
						<?php
						foreach($db->getUsers() as $u) {
							$selected = '';
							if($r != null) foreach($db->getUserRoles($u->id) as $role) {
								if($r->id == $role->role_id) {
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
				<th></th>
				<td><button class="fullwidth" title="Rolle Döner - Bester Döner!"><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
