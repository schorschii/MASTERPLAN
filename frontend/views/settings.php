<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

// create/update
$result = null;
if(($result==null || $result) && isset($_POST['ics_domain'])) {
	$result = $db->updateSetting('ics_domain', $_POST['ics_domain']);
}
if(($result==null || $result) && isset($_POST['sc_absence'])) {
	$result = $db->updateSetting('sc_absence', $_POST['sc_absence']);
}
if(($result==null || $result) && isset($_POST['sc_swap'])) {
	$result = $db->updateSetting('sc_swap', $_POST['sc_swap']);
}
if(($result==null || $result) && isset($_POST['rest_period'])) {
	$result = $db->updateSetting('rest_period', $_POST['rest_period']);
}
if(($result==null || $result) && isset($_POST['absence_confirmation_required'])) {
	$result = $db->updateSetting('absence_confirmation_required', $_POST['absence_confirmation_required']);
}
if(($result==null || $result) && isset($_POST['absence_mails'])) {
	$result = $db->updateSetting('absence_mails', $_POST['absence_mails']);
}
if(($result==null || $result) && isset($_POST['swap_mails'])) {
	$result = $db->updateSetting('swap_mails', $_POST['swap_mails']);
}
if(($result==null || $result) && isset($_POST['api_active'])) {
	$result = $db->updateSetting('api_active', $_POST['api_active']);
}
if(($result==null || $result) && isset($_POST['api_key'])) {
	$result = $db->updateSetting('api_key', $_POST['api_key']);
}
if(($result==null || $result) && isset($_POST['api_allowed_ips'])) {
	$result = $db->updateSetting('api_allowed_ips', $_POST['api_allowed_ips']);
}
if($result != null) {
	if($result) {
		$info = LANG['settings_saved'];
		$infoclass = 'green';
	} else {
		$info = LANG['error'].': '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}

// bg image
$bgfile = TMP_FILES.'/'.'bg.image';
if(isset($_POST['remove_bg_img']) && $_POST['remove_bg_img']) {
	unlink($bgfile);
	header('Location: index.php?view=settings');
	die();
}
if(isset($_FILES['bg_img'])) {
	if(move_uploaded_file($_FILES["bg_img"]["tmp_name"], $bgfile)) {
		$info = LANG['background_image_saved'];
		$infoclass = 'green';
	} else {
		$info = LANG['background_image_could_not_be_saved'];
		$infoclass = 'red';
	}
}

// license
if(isset($_FILES['license_file'])) {
	$file = TMP_FILES.'/'.'license';
	if(move_uploaded_file($_FILES["license_file"]["tmp_name"], $file)) {
		header('Location: index.php?view=settings');
		die();
	} else {
		$info = LANG['license_file_could_not_be_saved'];
		$infoclass = 'red';
	}
}

// display
$prefillDomain = $db->getSetting('ics_domain');
$precheckScSwap = boolval($db->getSetting('sc_swap'));
$precheckScAbsence = boolval($db->getSetting('sc_absence'));
$prefillRestPeriod = $db->getSetting('rest_period') ?? -1;
$precheckAbsenceConfirmationRequired = intval($db->getSetting('absence_confirmation_required'));
$precheckAbsenceMails = boolval($db->getSetting('absence_mails'));
$precheckSwapMails = boolval($db->getSetting('swap_mails'));
$precheckApiActive = boolval($db->getSetting('api_active'));
$prefillApiKey = $db->getSetting('api_key');
$prefillApiAllowedIps = $db->getSetting('api_allowed_ips');
?>

<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class='contentbox small'>
	<h2><?php echo LANG['autoplan']; ?></h2>
	<img class="contentbox-embleme" src="img/flash-auto.svg">
	<form method="POST">
		<div>
			<label>
				<?php echo LANG['idle_time_hours']; ?>:&nbsp;
				<input type="number" name="rest_period" value="<?php echo htmlspecialchars($prefillRestPeriod); ?>">
				<div class="hint">
					<?php echo LANG['idle_time_hours_description']; ?>
				</div>
				<div class="hint">
					<?php echo LANG['idle_time_hours_description2']; ?>
				</div>
			</label>
		</div>

		<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button>
	</form>
</div>

<div class='contentbox small'>
	<h2><?php echo LANG['self_care_portal']; ?></h2>
	<img class="contentbox-embleme" src="img/users.svg">
	<form method="POST">
		<div>
			<label>
				<input type="hidden" name="sc_absence" value="0">
				<input type="checkbox" name="sc_absence" value="1" <?php echo $precheckScAbsence ? 'checked="true"' : ''; ?>>
				<?php echo LANG['enable_enter_absences']; ?>
			</label>
		</div>
		<div>
			<label>
				<input type="hidden" name="sc_swap" value="0">
				<input type="checkbox" name="sc_swap" value="1" <?php echo $precheckScSwap ? 'checked="true"' : ''; ?>>
				<?php echo LANG['enable_service_swap']; ?>
			</label>
		</div>
		<div>
			<label>
				<input type="hidden" name="swap_mails" value="0">
				<input type="checkbox" name="swap_mails" value="1" <?php echo $precheckSwapMails ? 'checked="true"' : ''; ?>>
				<?php echo LANG['send_emails_on_new_service_swap']; ?>
				<div class="hint">
					<?php echo LANG['send_emails_on_new_service_swap_description']; ?>
				</div>
			</label>
		</div>

		<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button>
	</form>
</div>

<div class='contentbox small'>
	<h2><?php echo LANG['email']; ?></h2>
	<img class="contentbox-embleme" src="img/email.svg">
	<form method="POST">
		<table>
			<tr>
				<th><?php echo LANG['domain_for_invitation_mails']; ?></th>
				<td><input type="text" name="ics_domain" class="fullwidth" placeholder="example.com" value="<?php echo htmlspecialchars($prefillDomain); ?>"></td>
			</tr>
		</table>
		<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button>
	</form>
	<p>
		<a href="?view=editTextTemplates" class="button fullwidth"><img src="img/template.svg">&nbsp;<?php echo LANG['edit_templates']; ?></a>
	</p>
</div>

<div class="contentbox small">
	<h2><?php echo LANG['api']; ?></h2>
	<img class="contentbox-embleme" src="img/code.svg">
	<form method="POST">
		<table>
			<tr>
				<th><?php echo LANG['status']; ?>:</th>
				<td>
					<label>
						<input type="hidden" name="api_active" value="0">
						<input type="checkbox" name="api_active" value="1" <?php echo $precheckApiActive ? 'checked="true"' : ''; ?>>&nbsp;<?php echo LANG['enable_api']; ?>
					</label>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['api_key']; ?>:</th>
				<td><input type="text" name="api_key" value="<?php echo htmlspecialchars($prefillApiKey); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['ip_whitelist']; ?>:</th>
				<td><input type="text" name="api_allowed_ips" value="<?php echo htmlspecialchars($prefillApiAllowedIps); ?>"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
		</table>
	</form>
</div>

<div class="contentbox small">
	<h2><?php echo LANG['background_image']; ?></h2>
	<img class="contentbox-embleme" src="img/image.svg">
	<form method="POST" enctype='multipart/form-data'>
		<table>
			<tr>
				<th><?php echo LANG['change']; ?>:</th>
				<td><input type="file" name="bg_img"></td>
			</tr>
			<tr>
				<th></th>
				<td><label><input type="checkbox" name="remove_bg_img">&nbsp;<?php echo LANG['remove_uploaded_image']; ?></label></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['apply']; ?></button></td>
			</tr>
		</table>
	</form>
</div>

<div class="contentbox small">
	<h2><?php echo LANG['license']; ?></h2>
	<img class="contentbox-embleme" src="img/key.svg">
	<?php if($lic->licenseValid) { ?>
		<div class="infobox green"><?php echo $lic->licenseText; ?></div>
	<?php } else { ?>
		<div class="infobox red"><?php echo $lic->licenseText; ?></div>
	<?php } ?>
	<form method="POST" enctype='multipart/form-data'>
		<table>
			<tr>
				<th><?php echo LANG['licensee']; ?>:</th>
				<td><input type="text" disabled="true" value="<?php echo htmlspecialchars($lic->licenseCompany); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['valid_to']; ?>:</th>
				<td><input type="text" disabled="true" value="<?php echo htmlspecialchars(strftime(DATE_FORMAT, $lic->licenseExpireTime)); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['licensed_users']; ?>:</th>
				<td><input type="number" disabled="true" value="<?php echo htmlspecialchars($lic->licenseUsers); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['import_license']; ?>:</th>
				<td><input type="file" name="license_file"></td>
			</tr>
			<tr>
				<td><a href="https://georg-sieber.de/?page=masterplan" target="_blank"><?php echo LANG['buy_license']; ?></a></td>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['upload']; ?></button></td>
			</tr>
		</table>
	</form>
</div>

<div class="contentbox small">
	<h2><?php echo LANG['absences']; ?></h2>
	<img class="contentbox-embleme" src="img/absent.svg">
	<p>
		<form method="POST">
			<div class="margintop">
				<label>
					<input type="radio" name="absence_confirmation_required" value="0" <?php echo ($precheckAbsenceConfirmationRequired==0) ? 'checked="true"' : ''; ?>>
					<?php echo LANG['no_confirmation_approval']; ?>
					<div class="hint">
						<?php echo LANG['no_confirmation_approval_description']; ?>
					</div>
				</label>
			</div>
			<div class="margintop">
				<label>
					<input type="radio" name="absence_confirmation_required" value="1" <?php echo ($precheckAbsenceConfirmationRequired==1) ? 'checked="true"' : ''; ?>>
					<?php echo LANG['approval_by_roster_admin']; ?>
					<div class="hint">
						<?php echo LANG['approval_by_roster_admin_description']; ?>
					</div>
				</label>
			</div>
			<div class="margintop">
				<label>
					<input type="radio" name="absence_confirmation_required" value="2" <?php echo ($precheckAbsenceConfirmationRequired==2) ? 'checked="true"' : ''; ?>>
					<?php echo LANG['approval_by_roster_admin_and_confirmation_by_superadmin']; ?>
					<div class="hint">
						<?php echo LANG['approval_by_roster_admin_and_confirmation_by_superadmin_description']; ?>
					</div>
				</label>
			</div>

			<div class="margintop">
				<label>
					<input type="hidden" name="absence_mails" value="0">
					<input type="checkbox" name="absence_mails" value="1" <?php echo $precheckAbsenceMails ? 'checked="true"' : ''; ?>>
					<?php echo LANG['automatically_send_emails']; ?>
					<div class="hint">
						<?php echo LANG['absences_automatically_send_emails_description']; ?>
					</div>
				</label>
			</div>
			<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button>
		</form>
	</p>
	<p>
		<a href="?view=editAbsentTypes" class="button fullwidth"><img src="img/absent.svg">&nbsp;<?php echo LANG['define_absence_types']; ?></a>
	</p>
</div>
