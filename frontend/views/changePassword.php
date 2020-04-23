<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

if($currentUser->ldap > 0) {
	die('<div class="infobox yellow">Sie sind mit einem LDAP-Account angemeldet. Bitte ändern Sie Ihr Kennwort über Ihren Verzeichnisdienst.</div>');
}

if(isset($_POST['old_pw']) && isset($_POST['new_pw']) && isset($_POST['confirm_pw'])) {
	$error = false;

	if(!$error) if(!password_verify($_POST['old_pw'], $currentUser->password)) {
		$info = 'Altes Kennwort nicht korrekt';
		$infoclass = 'red';
		$error = true;
	}
	if(!$error) if($_POST['new_pw'] != $_POST['confirm_pw']) {
		$info = 'Neue Kennwörter stimmen nicht überein';
		$infoclass = 'red';
		$error = true;
	}

	if(!$error) if($db->updateUserPassword(
		$currentUser->id,
		password_hash($_POST['new_pw'], PASSWORD_DEFAULT)
	)) {
		$info = 'Kennwort geändert';
		$infoclass = 'green';
	} else {
		$info = 'Kennwort konnte nicht geändert werden: '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}
?>
<div class="contentbox small">
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST" class="marginbottom">
		<table>
			<tr>
				<th>Aktuelles Kennwort:</th>
				<td>
					<input type="password" name="old_pw" autofocus="true">
				</td>
			</tr>
			<tr>
				<th>Neues Kennwort:</th>
				<td>
					<input type="password" name="new_pw" value="">
				</td>
			</tr>
			<tr>
				<th>Kennwort wiederholen:</th>
				<td>
					<input type="password" name="confirm_pw" value="">
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;Ändern</button></td>
			</tr>
		</table>
	</form>
</div>
