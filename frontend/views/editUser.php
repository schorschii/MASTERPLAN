<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
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
	$info = "Als Standard für neue Benutzer gespeichert";
	$infoclass = "green";
}
elseif(!empty($_POST['login'])) {
	// create/update user
	if(empty($_POST['id']) && $db->getUserByLogin($_POST['login'])) {
		$info = "Ein Benutzer mit dem Anmeldenamen '".$_POST['login']."' existiert bereits";
		$infoclass = "red";
	} elseif(isset($_POST['id']) && $_POST['id'] == $_SESSION['mp_userid'] && $_POST['superadmin'] != 1) {
		$info = "Sie können sich nicht selbst die Superadmin-Berechtigung entziehen - dies muss durch einen anderen Superadmin erfolgen.";
		$infoclass = "red";
	} elseif(isset($_POST['id']) && $_POST['id'] == $_SESSION['mp_userid'] && $_POST['locked'] > 0) {
		$info = "Sie können sich nicht selbst sperren - dies muss durch einen anderen Superadmin erfolgen.";
		$infoclass = "red";
	} elseif(trim($_POST['fullname']) === "") {
		$info = "Der Anzeigename darf nicht leer sein";
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
			$info = "Benutzer konnte nicht gespeichert werden: ".$db->getLastStatement()->error;
			$infoclass = "red";
		} else {
			if(isset($_POST['id'])) {
				header('Location: index.php?view=users');
				die();
			} else {
				$info = "Benutzer gespeichert";
				$infoclass = "green";
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
	if($u == null) die('<div class="infobox red">Benutzer nicht gefunden</div>');
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
	<h2>Benutzereinstellungen</h2>
	<?php if($prefillLdap) { ?>
		<div class="infobox gray">Sie bearbeiten einen LDAP-Account. Einige Felder können nur über Ihren Verzeichnisdienst geändert werden.</div>
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
				<th>Anmeldename:</th>
				<td><input type="text" name="login" autofocus="true" value="<?php echo htmlspecialchars($prefillLogin); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Vorname:</th>
				<td><input type="text" id="txtFirstName" oninput="fillFullName()" name="firstname" value="<?php echo htmlspecialchars($prefillFirstName); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Nachname:</th>
				<td><input type="text" id="txtLastName" oninput="fillFullName()" name="lastname" value="<?php echo htmlspecialchars($prefillLastName); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Anzeigename:</th>
				<td><input type="text" id="txtFullName" name="fullname" value="<?php echo htmlspecialchars($prefillFullName); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>E-Mail Adresse:</th>
				<td><input type="email" name="email" value="<?php echo htmlspecialchars($prefillEmail); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Telefon:</th>
				<td><input type="text" name="phone" value="<?php echo htmlspecialchars($prefillPhone); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Mobiltelefon:</th>
				<td><input type="text" name="mobile" value="<?php echo htmlspecialchars($prefillMobile); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Geburtstag:</th>
				<td><input type="date" name="birthday" value="<?php echo htmlspecialchars($prefillBirthday); ?>"></td>
			</tr>
			<tr>
				<th>Arbeitsbeginn:</th>
				<td><input type="date" name="start_date" value="<?php echo htmlspecialchars($prefillStartDate); ?>"></td>
			</tr>
			<tr>
				<th>Identifikationsnummer:</th>
				<td><input type="text" name="id_no" value="<?php echo htmlspecialchars($prefillIdNo); ?>"></td>
			</tr>
			<tr>
				<th>Beschreibung:</th>
				<td><input type="text" name="description" value="<?php echo htmlspecialchars($prefillDescription); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Kennwort:</th>
				<td><input type="password" name="password" value="<?php echo htmlspecialchars($prefillPassword); ?>" <?php echo $prefillLdap ? "readonly='true'" : ""; ?>></td>
			</tr>
			<tr>
				<th>Max. Std./Tag:</th>
				<td><input type="number" name="max_hours_per_day" <?php if($userRole!=null) echo 'readonly="true"'; ?> value="<?php echo $prefillMaxHoursPerDay; ?>"></td>
			</tr>
			<tr>
				<th>Max. Dienste/Woche:</th>
				<td><input type="number" name="max_services_per_week" <?php if($userRole!=null) echo 'readonly="true"'; ?> value="<?php echo $prefillMaxServicesPerWeek; ?>"></td>
			</tr>
			<tr>
				<th>Max. Std./Woche:</th>
				<td><input type="number" name="max_hours_per_week" <?php if($userRole!=null) echo 'readonly="true"'; ?> value="<?php echo $prefillMaxHoursPerWeek; ?>"></td>
			</tr>
			<tr>
				<th>Max. Std./Monat:</th>
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
				<th>Farbe:</th>
				<td><input type="color" name="color" value="<?php echo htmlspecialchars($prefillColor); ?>"></td>
			</tr>
			<tr>
				<th>
					Zugeteilte Dienstpläne:
					<div class="hint">
						<div>Wählen Sie die Dienstpläne aus, in denen diese Person eingesetzt werden soll.</div>
						<div>Halten Sie STRG gedrückt, um mehrere Dienstpläne auszuwählen.</div>
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
					Dienstplanadmin für:
					<div class="hint">
						Dienstplanadmins dürfen Dienste für die angegebenen Dienstpläne planen.
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
				<th>Account-Sperre:</th>
				<td class="padding">
					<label>
						<input type="hidden" name="locked" value="0">
						<input type="checkbox" name="locked" value="1" <?php if($precheckLocked) echo 'checked="true"'; ?>>
						Benutzerkonto sperren
						<div class="hint">
							Gesperrte Benutzer können sich nicht mehr anmelden, jedoch weiterhin Diensten zugeordnet werden.
						</div>
					</label>
				</td>
			</tr>
			<tr>
				<th>Superadmin-Berechtigung:</th>
				<td class="padding">
					<label>
						<input type="hidden" name="superadmin" value="0">
						<input type="checkbox" name="superadmin" value="1" <?php if($precheckSuperadmin) echo 'checked="true"'; ?>>
						MASTERPLAN-Superadmin
						<div class="hint">
							Superadmins dürfen alle Dienstpläne, Dienste, Benutzer und globale Einstellungen verwalten.
						</div>
					</label>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button class="fullwidth"><img src='img/ok.svg'>&nbsp;Benutzer speichern</button></td>
			</tr>
			<tr>
				<th></th>
				<td><button class="fullwidth" onclick="action.value = 'template'"><img src='img/template.svg'>&nbsp;Als Standard festlegen</button></td>
			</tr>
		</table>
	</form>
	<?php if($u != null) { ?>
		<br>
		<form method='POST' action='index.php?view=editUserConstraint'>
			<input type='hidden' name='user' value='<?php echo $u->id; ?>'>
			<button class='fullwidth'><img src='img/warning.svg'>&nbsp;Beschränkungen bearbeiten</button>
		</form>
	<?php } ?>
</div>
