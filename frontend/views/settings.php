<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
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
		$info = "Einstellungen gespeichert";
		$infoclass = "green";
	} else {
		$info = "Einstellungen konnten nicht gespeichert werden";
		$infoclass = "red";
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
		$info = "Bild gespeichert - bitte leeren Sie Ihren Browsercache, falls das neue Bild beim nächsten Seitenaufruf noch nicht angezeigt wird";
		$infoclass = "green";
	} else {
		$info = "Bild konnten nicht gespeichert werden - bitte stellen Sie sicher, dass der Webserver-Benutzer Schreibrechte auf das /tmp-Verzeichnis innerhalb der MASTERPLAN-Installation hat";
		$infoclass = "red";
	}
}

// license
if(isset($_FILES['license_file'])) {
	$file = TMP_FILES.'/'.'license';
	if(move_uploaded_file($_FILES["license_file"]["tmp_name"], $file)) {
		header('Location: index.php?view=settings');
		die();
	} else {
		$info = "Lizenzdatei konnten nicht gespeichert werden - bitte stellen Sie sicher, dass der Webserver-Benutzer Schreibrechte auf das /tmp-Verzeichnis innerhalb der MASTERPLAN-Installation hat";
		$infoclass = "red";
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
	<h2>Automatische Planung</h2>
	<img class="contentbox-embleme" src="img/flash-auto.svg">
	<form method="POST">
		<div>
			<label>
				Ruhezeit (Stunden):&nbsp;
				<input type="number" name="rest_period" value="<?php echo htmlspecialchars($prefillRestPeriod); ?>">
				<div class="hint">
					Verhindert, dass ein Mitarbeiter für einen Frühdienst eingeteilt wird, wenn er am vorhergehenden Tag für einen Spätdienst eingeteilt war.
				</div>
				<div class="hint">
					Oftmals sind dies 11 Stunden. Wenn Sie den Wert auf -1 setzen wird die Ruhezeit nicht beachtet.
				</div>
			</label>
		</div>

		<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;Speichern</button>
	</form>
</div>

<div class='contentbox small'>
	<h2>Self Care Portal</h2>
	<img class="contentbox-embleme" src="img/users.svg">
	<form method="POST">
		<div>
			<label>
				<input type="hidden" name="sc_absence" value="0">
				<input type="checkbox" name="sc_absence" value="1" <?php echo $precheckScAbsence ? 'checked="true"' : ''; ?>>
				"Abwesenheit eintragen" aktivieren
			</label>
		</div>
		<div>
			<label>
				<input type="hidden" name="sc_swap" value="0">
				<input type="checkbox" name="sc_swap" value="1" <?php echo $precheckScSwap ? 'checked="true"' : ''; ?>>
				Diensttausch aktivieren
			</label>
		</div>
		<div>
			<label>
				<input type="hidden" name="swap_mails" value="0">
				<input type="checkbox" name="swap_mails" value="1" <?php echo $precheckSwapMails ? 'checked="true"' : ''; ?>>
				E-Mails bei neuen Tauschgesuchen versenden
				<div class="hint">
					Sendet eine E-Mail an alle dem Dienstplan zugewiesenen Mitarbeiter bei einem neuen Tauschgesuch.
				</div>
			</label>
		</div>

		<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;Speichern</button>
	</form>
</div>

<div class='contentbox small'>
	<h2>E-Mail</h2>
	<img class="contentbox-embleme" src="img/email.svg">
	<form method="POST">
		<h3>Domäne (Termineinladungs-Mails)</h3>
		<input type="text" name="ics_domain" class="fullwidth" placeholder="example.com" value="<?php echo htmlspecialchars($prefillDomain); ?>">

		<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;Speichern</button>
	</form>
	<p>
		<a href="?view=editTextTemplates" class="button fullwidth"><img src="img/template.svg">&nbsp;Vorlagen anpassen</a>
	</p>
</div>

<div class="contentbox small">
	<h2>API (Schnittstelle)</h2>
	<img class="contentbox-embleme" src="img/code.svg">
	<form method="POST">
		<table>
			<tr>
				<th>Status:</th>
				<td>
					<label>
						<input type="hidden" name="api_active" value="0">
						<input type="checkbox" name="api_active" value="1" <?php echo $precheckApiActive ? 'checked="true"' : ''; ?>>&nbsp;API aktivieren
					</label>
				</td>
			</tr>
			<tr>
				<th>API-Key:</th>
				<td><input type="text" name="api_key" value="<?php echo htmlspecialchars($prefillApiKey); ?>"></td>
			</tr>
			<tr>
				<th>IP-Freigaben<br>(kommasepariert):</th>
				<td><input type="text" name="api_allowed_ips" value="<?php echo htmlspecialchars($prefillApiAllowedIps); ?>"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Speichern</button></td>
			</tr>
		</table>
	</form>
</div>

<div class="contentbox small">
	<h2>Hintergrundbild</h2>
	<img class="contentbox-embleme" src="img/image.svg">
	<form method="POST" enctype='multipart/form-data'>
		<table>
			<tr>
				<th>Hintergrundbild ändern:</th>
				<td><input type="file" name="bg_img"></td>
			</tr>
			<tr>
				<th></th>
				<td><label><input type="checkbox" name="remove_bg_img">&nbsp;Hochgeladenes Bild entfernen</label></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Hochladen</button></td>
			</tr>
		</table>
	</form>
</div>

<div class="contentbox small">
	<h2>Lizenz</h2>
	<img class="contentbox-embleme" src="img/key.svg">
	<?php if($lic->licenseValid) { ?>
		<div class="infobox green"><?php echo $lic->licenseText; ?></div>
	<?php } else { ?>
		<div class="infobox red"><?php echo $lic->licenseText; ?></div>
	<?php } ?>
	<form method="POST" enctype='multipart/form-data'>
		<table>
			<tr>
				<th>Lizenznehmer:</th>
				<td><input type="text" disabled="true" value="<?php echo htmlspecialchars($lic->licenseCompany); ?>"></td>
			</tr>
			<tr>
				<th>Gültig bis:</th>
				<td><input type="text" disabled="true" value="<?php echo htmlspecialchars(strftime(DATE_FORMAT, $lic->licenseExpireTime)); ?>"></td>
			</tr>
			<tr>
				<th>Lizenzierte Benutzer:</th>
				<td><input type="number" disabled="true" value="<?php echo htmlspecialchars($lic->licenseUsers); ?>"></td>
			</tr>
			<tr>
				<th>Lizenzdatei einspielen:</th>
				<td><input type="file" name="license_file"></td>
			</tr>
			<tr>
				<td><a href="https://georg-sieber.de/?page=masterplan" target="_blank">Lizenzen kaufen</a></td>
				<td><button><img src='img/ok.svg'>&nbsp;Hochladen</button></td>
			</tr>
		</table>
	</form>
</div>

<div class="contentbox small">
	<h2>Abwesenheiten (Urlaub)</h2>
	<img class="contentbox-embleme" src="img/absent.svg">
	<p>
		<form method="POST">
			<div class="margintop">
				<label>
					<input type="radio" name="absence_confirmation_required" value="0" <?php echo ($precheckAbsenceConfirmationRequired==0) ? 'checked="true"' : ''; ?>>
					Keine Bestätigung/Genehmigung notwendig
					<div class="hint">
						Abwesenheiten sind sofort freigegeben und werden bei der Planung beachtet
					</div>
				</label>
			</div>
			<div class="margintop">
				<label>
					<input type="radio" name="absence_confirmation_required" value="1" <?php echo ($precheckAbsenceConfirmationRequired==1) ? 'checked="true"' : ''; ?>>
					Bestätigung durch Dienstplan-Admin
					<div class="hint">
						Abwesenheiten benötigen Bestätigung durch einen zugehörigen Dienstplan-Admin
					</div>
				</label>
			</div>
			<div class="margintop">
				<label>
					<input type="radio" name="absence_confirmation_required" value="2" <?php echo ($precheckAbsenceConfirmationRequired==2) ? 'checked="true"' : ''; ?>>
					Bestätigung durch Dienstplan-Admin und Genehmigung durch Superadmin
					<div class="hint">
						Abwesenheiten benötigen Bestätigung durch einen zugehörigen Dienstplan-Admin sowie eine Genehmigung durch einen Superadmin
					</div>
				</label>
			</div>

			<div class="margintop">
				<label>
					<input type="hidden" name="absence_mails" value="0">
					<input type="checkbox" name="absence_mails" value="1" <?php echo $precheckAbsenceMails ? 'checked="true"' : ''; ?>>
					Automatisch E-Mails versenden
					<div class="hint">
						An Dienstplan-Admins bei neu zu bestätigenden Abwesenheiten sowie an den beantragenden Mitarbeiter nach Genehmigung
					</div>
				</label>
			</div>
			<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;Speichern</button>
		</form>
	</p>
	<p>
		<a href="?view=editAbsentTypes" class="button fullwidth"><img src="img/absent.svg">&nbsp;Abwesenheitstypen definieren</a>
	</p>
</div>
