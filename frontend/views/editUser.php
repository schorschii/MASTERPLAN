<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

if(!empty($_POST['action']) && $_POST['action']=='template') {
	// set user defaults (template)
	$db->updateSetting('default_user_login', $_POST['login']);
	$db->updateSetting('default_user_firstname', $_POST['firstname']);
	$db->updateSetting('default_user_lastname', $_POST['lastname']);
	$db->updateSetting('default_user_fullname', $_POST['fullname']);
	$db->updateSetting('default_user_email', $_POST['email']);
	$db->updateSetting('default_user_phone', $_POST['phone']);
	$db->updateSetting('default_user_mobile', $_POST['mobile']);
	$db->updateSetting('default_user_birthday', $_POST['birthday']);
	$db->updateSetting('default_user_start_date', $_POST['start_date']);
	$db->updateSetting('default_user_id_no', $_POST['id_no']);
	$db->updateSetting('default_user_description', $_POST['description']);
	$db->updateSetting('default_user_password', $_POST['password']);
	$db->updateSetting('default_user_max_hours_per_day', $_POST['max_hours_per_day']);
	$db->updateSetting('default_user_max_services_per_week', $_POST['max_services_per_week']);
	$db->updateSetting('default_user_max_hours_per_week', $_POST['max_hours_per_week']);
	$db->updateSetting('default_user_max_hours_per_month', $_POST['max_hours_per_month']);
	$db->updateSetting('default_user_locked', $_POST['locked']);
	$db->updateSetting('default_user_superadmin', $_POST['superadmin']);
	$info = LANG['saved_as_default_for_new_users'];
	$infoclass = 'green';
}
elseif(!empty($_POST['login'])) {
	// create/update user
	if(empty($_POST['id']) && $db->getUserByLogin($_POST['login'])) {
		$info = str_replace('%1', $_POST['login'], LANG['user_already_exists']);
		$infoclass = "red";
	} elseif(isset($_POST['id']) && $_POST['id'] == $_SESSION['mp_userid'] && $_POST['superadmin'] != 1) {
		$info = LANG['you_cannot_revoke_superadmin_rights_yourself'];
		$infoclass = "red";
	} elseif(isset($_POST['id']) && $_POST['id'] == $_SESSION['mp_userid'] && $_POST['locked'] > 0) {
		$info = LANG['you_cannot_lock_yourself'];
		$infoclass = "red";
	} elseif(trim($_POST['fullname']) === "") {
		$info = LANG['display_name_cannot_be_empty'];
		$infoclass = "red";
	} else {
		$error = false;

		$id = null;
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			if(!$db->updateUser(
				$id,
				$_POST['superadmin'],
				$_POST['login'],
				$_POST['firstname'],
				$_POST['lastname'],
				$_POST['fullname'],
				$_POST['email'],
				$_POST['phone'],
				$_POST['mobile'],
				empty($_POST['birthday']) ? null : $_POST['birthday'],
				empty($_POST['start_date']) ? null : $_POST['start_date'],
				$_POST['id_no'],
				$_POST['description'],
				$_POST['ldap'],
				$_POST['locked'],
				$_POST['max_hours_per_day'],
				$_POST['max_services_per_week'],
				$_POST['max_hours_per_week'],
				$_POST['max_hours_per_month'],
				$_POST['color']
			)) $error = true;
		} else {
			if(!$id = $db->createUser(
				$_POST['superadmin'],
				$_POST['login'],
				$_POST['firstname'],
				$_POST['lastname'],
				$_POST['fullname'],
				$_POST['email'],
				$_POST['phone'],
				$_POST['mobile'],
				empty($_POST['birthday']) ? null : $_POST['birthday'],
				empty($_POST['start_date']) ? null : $_POST['start_date'],
				$_POST['id_no'],
				$_POST['description'],
				$_POST['ldap'],
				$_POST['locked'],
				$_POST['max_hours_per_day'],
				$_POST['max_services_per_week'],
				$_POST['max_hours_per_week'],
				$_POST['max_hours_per_month'],
				$_POST['color']
			)) $error = true;
		}

		if(!$error && !empty($_POST['password']) && trim($_POST['password']) != '') {
			if(!$db->updateUserPassword(
				$id, password_hash($_POST['password'], PASSWORD_DEFAULT)
			)) $error = true;
		}

		if(!$error) {
			if($db->removeUserToRosterByUser($id)) {
				if(!empty($_POST['rosters'])) {
					foreach($_POST['rosters'] as $r_id) {
						if(!$db->insertUserToRoster(
							$id, $r_id
						)) $error = true;
					}
				}
			} else $error = true;
		}
		if(!$error) {
			if($db->removeUserToRosterAdminByUser($id)) {
				if(!empty($_POST['rosters_admin'])) {
					foreach($_POST['rosters_admin'] as $r_id) {
						if(!$db->insertUserToRosterAdmin(
							$id, $r_id
						)) $error = true;
					}
				}
			} else $error = true;
		}

		if($error) {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		} else {
			if(isset($_POST['id'])) {
				header('Location: index.php?view=users');
				die();
			} else {
				$info = LANG['user_saved'];
				$infoclass = 'green';
			}
		}
	}
}

// display
$prefillLogin = $db->getSetting('default_user_login', '');
$prefillFirstName = $db->getSetting('default_user_firstname', '');
$prefillLastName = $db->getSetting('default_user_lastname', '');
$prefillFullName = $db->getSetting('default_user_fullname', '');
$prefillEmail = $db->getSetting('default_user_email', '');
$prefillPhone = $db->getSetting('default_user_phone', '');
$prefillMobile = $db->getSetting('default_user_mobile', '');
$prefillBirthday = $db->getSetting('default_user_birthday', '');
$prefillStartDate = $db->getSetting('default_user_start_date', '');
$prefillIdNo = $db->getSetting('default_user_id_no', '');
$prefillDescription = $db->getSetting('default_user_description', '');
$prefillPassword = $db->getSetting('default_user_password', '');
$prefillLdap = 0;
$prefillMaxHoursPerDay = $db->getSetting('default_user_max_hours_per_dayn', '-1');
$prefillMaxServicesPerWeek = $db->getSetting('default_user_max_services_per_week', '-1');
$prefillMaxHoursPerWeek = $db->getSetting('default_user_max_hours_per_week', '-1');
$prefillMaxHoursPerMonth = $db->getSetting('default_user_max_hours_per_month', '-1');
$prefillColor = '#'.dechex(rand(20,210)).dechex(rand(20,210)).dechex(rand(20,210));
$preselectRosters = [];
$preselectRostersAdmin = [];
$precheckLocked = $db->getSetting('default_user_locked', '0');
$precheckSuperadmin = $db->getSetting('default_user_superadmin', '0');
$userRole = null;

$u = null;
if(isset($_GET['id'])) {
	$u = $db->getUser($_GET['id']);
	if($u == null) die('<div class="infobox red">'.LANG['not_found'].'</div>');
	$prefillLogin = $u->login;
	$prefillFirstName = $u->firstname;
	$prefillLastName = $u->lastname;
	$prefillFullName = $u->fullname;
	$prefillEmail = $u->email;
	$prefillPhone = $u->phone;
	$prefillMobile = $u->mobile;
	$prefillBirthday = $u->birthday;
	$prefillStartDate = $u->start_date;
	$prefillIdNo = $u->id_no;
	$prefillDescription = $u->description;
	$prefillPassword = '';
	$prefillLdap = $u->ldap;
	$prefillMaxHoursPerDay = $u->max_hours_per_day;
	$prefillMaxServicesPerWeek = $u->max_services_per_week;
	$prefillMaxHoursPerWeek = $u->max_hours_per_week;
	$prefillMaxHoursPerMonth = $u->max_hours_per_month;
	$prefillColor = $u->color;
	$preselectRosters = $db->getUserRosters($u->id);
	$preselectRostersAdmin = $db->getUserRostersAdmin($u->id);
	$precheckLocked = $u->locked;
	$precheckSuperadmin = $u->superadmin;
	$userRole = $roles->getUserRole($u->id);
}

function selectIfInRoster($roster, $user_rosters) {
	$str = "value='".$roster->id."'";
	foreach($user_rosters as $ur) {
		if($ur->roster_id == $roster->id) {
			$str .= " selected='true'";
			return $str;
		}
	}
	return $str;
}
?>

<script>
	function fillFullName() {
		txtFullName.value = txtFirstName.value+' '+txtLastName.value;
	}
</script>
<div class='contentbox small'>
	<h2><?php echo LANG['user_settings']; ?></h2>
	<?php if($prefillLdap) { ?>
		<div class="infobox gray"><?php echo LANG['ldap_account_notes']; ?></div>
	<?php } ?>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>

	<form method="POST" id="frmUser">
		<?php if($u != null) { ?>
			<input type="hidden" name="id" value="<?php echo $u->id; ?>">
		<?php } ?>
		<input type="hidden" name="ldap" value="<?php echo $prefillLdap; ?>">
		<input type="hidden" name="action" id="action" value="update">
		<table class="input">
			<tr>
				<th><?php echo LANG['login_name']; ?>:</th>
				<td><input type="text" name="login" autofocus="true" value="<?php echo htmlspecialchars($prefillLogin); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['first_name']; ?>:</th>
				<td><input type="text" id="txtFirstName" oninput="fillFullName()" name="firstname" value="<?php echo htmlspecialchars($prefillFirstName); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['surname']; ?>:</th>
				<td><input type="text" id="txtLastName" oninput="fillFullName()" name="lastname" value="<?php echo htmlspecialchars($prefillLastName); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['display_name']; ?>:</th>
				<td><input type="text" id="txtFullName" name="fullname" value="<?php echo htmlspecialchars($prefillFullName); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['email_address']; ?>:</th>
				<td><input type="email" name="email" value="<?php echo htmlspecialchars($prefillEmail); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['phone']; ?>:</th>
				<td><input type="text" name="phone" value="<?php echo htmlspecialchars($prefillPhone); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['mobile']; ?>:</th>
				<td><input type="text" name="mobile" value="<?php echo htmlspecialchars($prefillMobile); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['birthday']; ?>:</th>
				<td><input type="date" name="birthday" value="<?php echo htmlspecialchars($prefillBirthday); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['work_begin']; ?>:</th>
				<td><input type="date" name="start_date" value="<?php echo htmlspecialchars($prefillStartDate); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['identification_number']; ?>:</th>
				<td><input type="text" name="id_no" value="<?php echo htmlspecialchars($prefillIdNo); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['description']; ?>:</th>
				<td><input type="text" name="description" value="<?php echo htmlspecialchars($prefillDescription); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['password']; ?>:</th>
				<td><input type="password" name="password" value="<?php echo htmlspecialchars($prefillPassword); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_hrs_day']; ?>:</th>
				<td><input type="number" name="max_hours_per_day" <?php if($userRole!=null) echo 'readonly="true"'; ?> value="<?php echo $prefillMaxHoursPerDay; ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_services_week']; ?>:</th>
				<td><input type="number" name="max_services_per_week" <?php if($userRole!=null) echo 'readonly="true"'; ?> value="<?php echo $prefillMaxServicesPerWeek; ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_hrs_week']; ?>:</th>
				<td><input type="number" name="max_hours_per_week" <?php if($userRole!=null) echo 'readonly="true"'; ?> value="<?php echo $prefillMaxHoursPerWeek; ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['max_hrs_month']; ?>:</th>
				<td><input type="number" name="max_hours_per_month" <?php if($userRole!=null) echo 'readonly="true"'; ?> value="<?php echo $prefillMaxHoursPerMonth; ?>"></td>
			</tr>
			<?php if($userRole != null) { ?>
				<tr>
					<td colspan="2">
						<div class="infobox gray">Die deaktivierten Eingabefelder werden durch die Rolle "<?php echo htmlspecialchars($userRole->title); ?>" definiert.</div>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<th><?php echo LANG['color']; ?>:</th>
				<td><input type="color" name="color" value="<?php echo htmlspecialchars($prefillColor); ?>"></td>
			</tr>
			<tr>
				<th>
					<?php echo LANG['assigned_rosters']; ?>:
					<div class="hint">
						<div><?php echo LANG['assigned_rosters_description']; ?></div>
						<div><?php echo LANG['assigned_rosters_description2']; ?></div>
					</div>
				</th>
				<td>
					<select multiple="true" class="fullwidth" name="rosters[]">
					<?php foreach($db->getRosters() as $r) {
						echo "<option ".selectIfInRoster($r,$preselectRosters).">".htmlspecialchars($r->title)."</option>";
					} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<?php echo LANG['roster_admin_for']; ?>:
					<div class="hint">
						<?php echo LANG['roster_admin_description']; ?>
					</div>
				</th>
				<td>
					<select multiple="true" class="fullwidth" name="rosters_admin[]">
					<?php foreach($db->getRosters() as $r) {
						echo "<option ".selectIfInRoster($r,$preselectRostersAdmin).">".htmlspecialchars($r->title)."</option>";
					} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['account_lock']; ?>:</th>
				<td class="padding">
					<label>
						<input type="hidden" name="locked" value="0">
						<input type="checkbox" name="locked" value="1" <?php if($precheckLocked) echo 'checked="true"'; ?>>
						<?php echo LANG['lock_account']; ?>
						<div class="hint">
							<?php echo LANG['lock_account_description']; ?>
						</div>
					</label>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['superadmin_right']; ?>:</th>
				<td class="padding">
					<label>
						<input type="hidden" name="superadmin" value="0">
						<input type="checkbox" name="superadmin" value="1" <?php if($precheckSuperadmin) echo 'checked="true"'; ?>>
						<?php echo LANG['masterplan_superadmin']; ?>
						<div class="hint">
							<?php echo LANG['superadmin_description']; ?>
						</div>
					</label>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button class="fullwidth"><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
			<tr>
				<th></th>
				<td><button class="fullwidth" onclick="action.value = 'template'"><img src='img/template.svg'>&nbsp;<?php echo LANG['set_as_default']; ?></button></td>
			</tr>
		</table>
	</form>
	<?php if($u != null) { ?>
		<br>
		<form method='POST' action='index.php?view=editUserConstraint'>
			<input type='hidden' name='user' value='<?php echo $u->id; ?>'>
			<button class='fullwidth'><img src='img/warning.svg'>&nbsp;<?php echo LANG['edit_constraints']; ?></button>
		</form>
	<?php } ?>
</div>
