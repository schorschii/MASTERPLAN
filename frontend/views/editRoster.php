<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

// create/update
if(!empty($_POST['title'])) {
	$result = false;
	if(!empty($_POST['id'])) {
		$result = $db->updateRoster($_POST['id'], $_POST['title'], $_POST['autoplan_logic'], $_POST['ignore_working_hours'], $_POST['icsmail_sender_name'], $_POST['icsmail_sender_address']);
	} else {
		$result = $db->insertRoster($_POST['title'], $_POST['autoplan_logic'], $_POST['ignore_working_hours'], $_POST['icsmail_sender_name'], $_POST['icsmail_sender_address']);
	}
	if($result) {
		$info = LANG['roster_saved'];
		$infoclass = 'green';
	} else {
		$info = LANG['error'].': '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}

// display
$prefillTitle = '';
$prefillAutoplanLogic = 0;
$prefillIgnoreWorkingHours = 0;
$prefillIcsMailSenderName = '';
$prefillIcsMailSenderAddress = '';

$r = null;
if(isset($_GET['id'])) {
	$r = $db->getRoster($_GET['id']);
	if($r == null) die('<div class="infobox red">'.LANG['not_found'].'</div>');
	$prefillTitle = $r->title;
	$prefillAutoplanLogic = $r->autoplan_logic;
	$prefillIgnoreWorkingHours = $r->ignore_working_hours;
	$prefillIcsMailSenderName = $r->icsmail_sender_name;
	$prefillIcsMailSenderAddress = $r->icsmail_sender_address;
}
?>

<div class='contentbox small'>
<?php if($r == null) { ?>
	<h2><?php echo LANG['new_roster']; ?></h2>
<?php } else { ?>
	<h2><?php echo LANG['edit_roster']; ?></h2>
<?php } ?>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>
<form method="POST" id="frmNewRoster">
	<?php if($r != null) { ?>
		<input type="hidden" name="id" value="<?php echo $r->id; ?>">
	<?php } ?>
	<table>
		<tr>
			<th><?php echo LANG['title']; ?>:</th>
			<td><input type="text" name="title" autofocus="true" value="<?php echo htmlspecialchars($prefillTitle); ?>"></td>
		</tr>
		<tr>
			<th><?php echo LANG['autoplan_logic']; ?>:</th>
			<td>
				<select name="autoplan_logic">
					<option value="0" <?php if($prefillAutoplanLogic==0) echo "selected"; ?>>Mitarbeiter nach freien Kapazitäten verplanen</option>
					<option value="1" <?php if($prefillAutoplanLogic==1) echo "selected"; ?>>Mitarbeiter zufällig verplanen</option>
			</td>
		</tr>
		<tr>
			<th><?php echo LANG['specials']; ?>:</th>
			<td>
				<label>
					<input type="hidden" name="ignore_working_hours" value="0">
					<input type="checkbox" name="ignore_working_hours" value="1" <?php if($prefillIgnoreWorkingHours==1) echo "checked"; ?>>&nbsp;Arbeitsstunden-Beschränkung nicht berücksichtigen
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<th colspan="2"><h3><?php echo LANG['service_invitation_mail_options']; ?></h3></th>
		</tr>
		<tr>
			<th><?php echo LANG['sender_name']; ?>:</th>
			<td><input type="text" name="icsmail_sender_name" placeholder="(<?php echo LANG['optional']; ?>)" value="<?php echo htmlspecialchars($prefillIcsMailSenderName); ?>"></td>
		</tr>
		<tr>
			<th><?php echo LANG['sender_address']; ?>:</th>
			<td><input type="email" name="icsmail_sender_address" placeholder="(<?php echo LANG['optional']; ?>)" value="<?php echo htmlspecialchars($prefillIcsMailSenderAddress); ?>"></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<th></th>
			<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
		</tr>
	</table>
</form>
</div>
