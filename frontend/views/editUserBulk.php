<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

$users = [];
foreach(explode(',',$_GET['users']) as $user_id) {
	$user = $db->getUser($user_id);
	if($user != null) {
		$users[] = $user;
	}
}
if(count($users) == 0)
	die('<div class="infobox red">'.LANG['no_user_selected'].'</div>');

if(!empty($_POST['start_date'])
|| !empty($_POST['description'])
|| !empty($_POST['password'])
|| !empty($_POST['max_hours_per_day'])
|| !empty($_POST['max_services_per_week'])
|| !empty($_POST['max_hours_per_week'])
|| !empty($_POST['max_hours_per_month'])
|| !empty($_POST['color'])
) {
	$success = null;

	$db->beginTransaction();
	foreach($users as $u) {
		if($success === null || $success === true) {

			if(!empty($_POST['start_date']))
				$u->start_date = $_POST['start_date'];
			if(!empty($_POST['description']))
				$u->description = $_POST['description'];
			if(!empty($_POST['max_hours_per_day']))
				$u->max_hours_per_day = $_POST['max_hours_per_day'];
			if(!empty($_POST['max_services_per_week']))
				$u->max_services_per_week = $_POST['max_services_per_week'];
			if(!empty($_POST['max_hours_per_week']))
				$u->max_hours_per_week = $_POST['max_hours_per_week'];
			if(!empty($_POST['max_hours_per_month']))
				$u->max_hours_per_month = $_POST['max_hours_per_month'];
			if(!empty($_POST['color']))
				$u->color = $_POST['color'];

			$success = $db->updateUser(
				$u->id, $u->superadmin, $u->login, $u->firstname, $u->lastname, $u->fullname, $u->email, $u->phone, $u->mobile, $u->birthday, $u->start_date, $u->id_no, $u->description, $u->ldap, $u->locked, $u->max_hours_per_day, $u->max_services_per_week, $u->max_hours_per_week, $u->max_hours_per_month, $u->color
			);

			if(!empty($_POST['password'])) {
				$success = $db->updateUserPassword(
					$u->id, password_hash($_POST['password'], PASSWORD_DEFAULT)
				);
			}
		}
	}

	if($success) {
		$db->commitTransaction();
		header('Location: index.php?view=users');
		die();
	} else {
		$db->rollbackTransaction();
		$info = LANG['error'].': '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}
?>
<div class="contentbox small">
	<h2><?php echo LANG['edit_users']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
		<table>
			<tr>
				<th><?php echo LANG['selected_users']; ?>:</th>
				<td>
					<input type="text" disabled="true" value="<?php echo count($users); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="start_date.disabled = !this.checked">
						<?php echo LANG['work_begin']; ?>:
					</label>
				</th>
				<td><input type="date" name="start_date" id="start_date" disabled="true"></td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="description.disabled = !this.checked">
						<?php echo LANG['description']; ?>:
					</label>
				</th>
				<td><input type="text" name="description" id="description" disabled="true"></td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="password.disabled = !this.checked">
						<?php echo LANG['password']; ?>:
					</label>
				</th>
				<td><input type="password" name="password" id="password" disabled="true"></td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="max_hours_per_day.disabled = !this.checked">
						<?php echo LANG['max_hrs_day']; ?>:
					</label>
				</th>
				<td><input type="number" name="max_hours_per_day" id="max_hours_per_day" value="-1" disabled="true"></td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="max_services_per_week.disabled = !this.checked">
						<?php echo LANG['max_services_week']; ?>:
					</label>
				</th>
				<td><input type="number" name="max_services_per_week" id="max_services_per_week" value="-1" disabled="true"></td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="max_hours_per_week.disabled = !this.checked">
						<?php echo LANG['max_hrs_week']; ?>:
					</label>
				</th>
				<td><input type="number" name="max_hours_per_week" id="max_hours_per_week" value="-1" disabled="true"></td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="max_hours_per_month.disabled = !this.checked">
						<?php echo LANG['max_hrs_month']; ?>:
					</label>
				</th>
				<td><input type="number" name="max_hours_per_month" id="max_hours_per_month" value="-1" disabled="true"></td>
			</tr>
			<tr>
				<th>
					<label>
						<input type="checkbox" onchange="color.disabled = !this.checked">
						<?php echo LANG['color']; ?>:
					</label>
				</th>
				<td><input type="color" name="color" id="color" value="#ececec" disabled="true"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['apply']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
