<?php
require_once('../lib/loader.php');

$info = null;
$infoclass = null;

$schemaExists = $db->existsSchema();

if($schemaExists && count($db->getUsers()) > 0) die();

if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2'])) {
	if($_POST['password'] == $_POST['password2']) {
		$id = $db->createUser(
			1, // superadmin-flag
			$_POST['username'], // login
			'', // firstname
			'', // lastname
			'Administrator', // fullname
			'', // email
			'', // phone
			'', // mobile
			null, // birthday
			date('Y-m-d'), // start date
			'', // id_no
			'initial admin user', // description
			0, // ldap-flag
			0, // locked-flag
			-1, // max hours per day
			-1, // may services per week
			-1, // may hours per week
			-1, // max hours per month
			'#fff' // color
		);
		$db->updateUserPassword(
			$id, password_hash($_POST['password'], PASSWORD_DEFAULT)
		);
		header('Location: index.php');
		die();
	} else {
		$info = LANG['passwords_do_not_match'];
		$infoclass = 'yellow';
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo LANG['app_name']; ?></title>
	<?php require('head.inc.php'); ?>
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
			<?php if($schemaExists) { ?>
			<div class="infobox">
				<?php echo LANG['welcome_to_masterplan']; ?>
				<br>
				<?php echo LANG['please_choose_username_and_password_for_superadmin_user']; ?>
			</div>
			<form method="POST">
				<input type="text" class="fullwidth" id="txtUsername" name="username" placeholder="Anmeldename">
				<input type="password" class="fullwidth" id="txtPassword" name="password" placeholder="Neues Kennwort">
				<input type="password" class="fullwidth" id="txtPassword2" name="password2" placeholder="Kennwort bestätigen">
				<button id="btnSubmit" class="fullwidth">Einrichten</button>
			</form>
		<?php } else { ?>
			<div class="infobox">
				<?php echo LANG['welcome_to_masterplan']; ?>
				<br>
				<?php echo LANG['please_import_database_schema']; ?>
			</div>
		<?php } ?>
		</div>
	</div>
	<div id="vendorcontainer">
		<a href="https://georg-sieber.de" target="_blank"><img src="img/vendor.png" id="vendor"></a>
	</div>
</body>
</html>
