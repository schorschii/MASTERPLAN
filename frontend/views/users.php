<?php
$info = null;
$infoclass = null;
$ldapResult = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeUser' && !empty($_POST['id'])) {
		if($db->removeUser($_POST['id'])) {
			$info = LANG['user_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
	elseif($_POST['action'] == 'sync_ldap') {
		ob_start();
		require('../lib/ldapsync.php');
		$ldapResult = str_replace("\n", "<br>", ob_get_clean());
		$info = LANG['ldap_sync_finished'];
		$infoclass = '';
	}
}
?>

<script>
function bulkEditUsers() {
	var user_ids = [];
	var users = document.getElementsByClassName('user');
	for(i=0; i<users.length; i++) {
		if(users[i].checked)
			user_ids.push(users[i].value);
	}
	var page = 'index.php?view=editUserBulk';
	var param = urlencodeObject({
		'users' : user_ids.join(',')
	});
	window.location.replace( page + '&' + param );
}
</script>

<div class='contentbox'>
<h2><?php echo LANG['existing_users']; ?> (<?php echo count($db->getUsers()); ?>)</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>
<?php if($ldapResult != null) { ?>
	<div style="display:none;"><?php echo $ldapResult; ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='editUser'>
		<button><img src='img/add.svg'>&nbsp;<?php echo LANG['user']; ?></button>
	</form>
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='userBirthdays'>
		<button><img src='img/birthday.svg'>&nbsp;<?php echo LANG['birthday_overview']; ?></button>
	</form>
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='userAnniversaries'>
		<button><img src='img/anniversary.svg'>&nbsp;<?php echo LANG['anniversaries']; ?></button>
	</form>
	<form method="POST" class="inlineblock">
		<input type="hidden" name="action" value="sync_ldap">
		<button <?php if(LDAP_SERVER == null) echo 'disabled="true"'; ?>><img src="img/sync.svg">&nbsp;<?php echo LANG['start_ldap_sync']; ?></button>
	</form>
	<form method='GET' class='inlineblock'>
		<button type='button' onclick='bulkEditUsers()'><img src='img/edit.svg'>&nbsp;<?php echo LANG['edit_selected_users']; ?></button>
	</form>
</div>

<table class="data rowhover">
	<tr>
		<th><?php echo LANG['color']; ?></th><th><?php echo LANG['login_name']; ?></th><th><?php echo LANG['surname']; ?></th><th><?php echo LANG['first_name']; ?></th><th><?php echo LANG['display_name']; ?></th><th><?php echo LANG['email']; ?></th><th><?php echo LANG['action']; ?></th>
	</tr>
	<?php
	foreach($db->getUsers() as $u) {
		echo '<tr>';
		echo '<td>';
		echo ' <input type="checkbox" class="user" name="users[]" value="'.$u->id.'">';
		echo ' <span class="colorpreview" style="background-color:'.htmlspecialchars($u->color).'"></span>';
		echo '</td>';
		echo '<td>'
			.($u->superadmin ? '<img title="Superadmin" src="img/settings.svg">' : '')
			.($u->ldap ? '<img title="LDAP-Account" src="img/ldap-directory.svg">' : '')
			.($u->locked ? '<img title="Gesperrt" src="img/lock.svg">' : '')
			.htmlspecialchars($u->login)
			.'</td>';
		echo '<td>'.htmlspecialchars($u->lastname).'</td>';
		echo '<td>'.htmlspecialchars($u->firstname).'</td>';
		echo '<td>'.htmlspecialchars($u->fullname).'</td>';
		echo '<td><a href="mailto:'.htmlspecialchars($u->email).'">'.htmlspecialchars($u->email).'</a></td>';
		echo '<td class="wrapcontent">';
		echo '<a class="button" href="?view=editUser&id='.$u->id.'" title="'.LANG['edit'].'"><img src="img/edit.svg"></a>';
		if($u->id != $currentUser->id)
		echo '<form method="POST" onsubmit="return confirm('."'".LANG['really_remove_this_user']."'".')">'
			.'<input type="hidden" name="action" value="removeUser">'
			.'<input type="hidden" name="id" value="'.$u->id.'">'
			.'<button title="'.LANG['remove'].'"><img src="img/delete.svg"></button>'
			.'</form>';
		echo '</td>';
		echo '</tr>';
	}
	?>
</table>
</div>
