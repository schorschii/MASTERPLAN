<?php
require_once('../lib/loader.php');

$info = null;
$infoclass = null;

// check if setup needed
if(count($db->getUsers()) == 0)
	header('Location: setup.php');

// check browser
$browserFail = null;
$b = new browser();
if(!$b->valid) $browserFail = $b->message;

// execute login if requested
session_start();
if(isset($_POST['username']) && isset($_POST['password'])) {
	require_once('../lib/loader.php');
	$user = $db->getUserByLogin($_POST['username']);
	if($user == null) {
		sleep(2);
		$info = LANG['user_does_not_exist'];
		$infoclass = 'red';
	} elseif($user->locked > 0) {
		$info = LANG['user_locked'];
		$infoclass = 'red';
	} else {
		if(validatePassword($user, $_POST['password'])) {
			$_SESSION['mp_login'] = $user->login;
			$_SESSION['mp_userid'] = $user->id;
			$_SESSION['mp_installation'] = dirname(__FILE__);
			header('Location: index.php');
			die();
		} else {
			sleep(2);
			$info = LANG['login_failed'];
			$infoclass = 'red';
		}
	}
}
elseif(isset($_GET['logout'])) {
	if(isset($_SESSION['mp_login'])) {
		session_destroy();
		$info = LANG['logout_successful'];
		$infoclass = 'green';
	}
}

function validatePassword($userObject, $checkPassword) {
	if($userObject->ldap) {
		if(empty($checkPassword)) return false;
		$ldapconn = ldap_connect(LDAP_SERVER);
		if(!$ldapconn) return false;
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 3);
		$ldapbind = @ldap_bind($ldapconn, $userObject->login.'@'.LDAP_DOMAIN, $checkPassword);
		if(!$ldapbind) return false;
		return true;
	} else {
		return password_verify($checkPassword, $userObject->password);
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo LANG['app_name']; ?></title>
	<?php require('head.inc.php'); ?>
	<?php if(file_exists(TMP_FILES.'/'.'bg.image')) { ?>
		<style>
		html, body {
			background-image: url("../tmp/bg.image");
		}
		</style>
	<?php } ?>
	<script>
	function beginFadeOutAnimation() {
		btnSubmit.setAttribute('disabled', 'true');
		txtUsername.setAttribute('readonly', 'true');
		txtPassword.setAttribute('readonly', 'true');
	}
	</script>
</head>
<body>
	<div id="container">
		<div id="splash"
		<?php if(file_exists('img/cdtitle.jpg')) { ?>style="background-image:url('img/cdtitle.jpg')"<?php } ?>>
			<div id="logocontainer">
				<img src="img/logo.png" id="logo">
			</div>
			<?php if($info != null) { ?>
				<div class="infobox <?php echo $infoclass; ?>"><?php echo $info; ?></div>
			<?php } ?>
			<?php if($browserFail == null) { ?>
			<form method="POST" onsubmit="beginFadeOutAnimation()" class="flex">
				<input type="text" id="txtUsername" name="username" placeholder="<?php echo LANG['username']; ?>" class="flex-fill" autofocus="true">
				<input type="password" id="txtPassword" name="password" placeholder="<?php echo LANG['password']; ?>" class="flex-fill">
				<button id="btnSubmit"><?php echo LANG['login']; ?></button>
			</form>
			<?php } else { ?>
				<div class="infobox yellow"><?php echo $browserFail; ?></div>
			<?php } ?>
		</div>
	</div>
	<div id="vendorcontainer">
		<a href="https://georg-sieber.de" target="_blank"><img src="img/vendor.png" id="vendor"></a>
	</div>
</body>
</html>
