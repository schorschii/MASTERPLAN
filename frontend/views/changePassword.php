<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

if($currentUser->ldap > 0) {
	die('<div class="infobox yellow">'.LANG['unable_to_change_password_ldap'].'</div>');
}

if(isset($_POST['old_pw']) && isset($_POST['new_pw']) && isset($_POST['confirm_pw'])) {
	$error = false;

	if(!$error) if(!password_verify($_POST['old_pw'], $currentUser->password)) {
		$info = LANG['old_password_not_correct'];
		$infoclass = 'red';
		$error = true;
	}
	if(!$error) if($_POST['new_pw'] != $_POST['confirm_pw']) {
		$info = LANG['passwords_do_not_match'];
		$infoclass = 'red';
		$error = true;
	}

	if(!$error) if($db->updateUserPassword(
		$currentUser->id,
		password_hash($_POST['new_pw'], PASSWORD_DEFAULT)
	)) {
		$info = LANG['password_changed'];
		$infoclass = 'green';
	} else {
		$info = LANG['password_could_not_be_changed'].' '.$db->getLastStatement()->error;
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
				<th><?php echo LANG['current_password']; ?>:</th>
				<td>
					<input type="password" name="old_pw" autofocus="true">
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['new_password']; ?>:</th>
				<td>
					<input type="password" name="new_pw" value="">
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['repeat_password']; ?>:</th>
				<td>
					<input type="password" name="confirm_pw" value="">
				</td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['change']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
