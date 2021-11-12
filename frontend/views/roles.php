<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeRole' && !empty($_POST['id'])) {
		if($db->removeRole($_POST['id'])) {
			$info = LANG['role_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
}
?>

<div class='contentbox'>
<h2><?php echo LANG['existing_roles']; ?> (<?php echo count($db->getRoles()); ?>)</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='editRole'>
		<button><img src='img/add.svg'>&nbsp;<?php echo LANG['role']; ?></button>
	</form>
</div>

<table class="data rowhover">
	<tr>
		<th><?php echo LANG['title']; ?></th><th><?php echo LANG['max_hrs_day']; ?></th><th><?php echo LANG['max_services_week']; ?></th><th><?php echo LANG['max_hrs_week']; ?></th><th><?php echo LANG['max_hrs_month']; ?></th><th><?php echo LANG['number_of_employees']; ?></th><th><?php echo LANG['action']; ?></th>
	</tr>
	<?php
	foreach($db->getRoles() as $r) {
		// count assigned users
		$amount_users = 0;
		foreach($db->getUsers() as $u) {
			if($r != null) foreach($db->getUserRoles($u->id) as $role) {
				if($r->id == $role->role_id) {
					$amount_users ++;
				}
			}
		}

		echo '<tr>';
		echo '<td>'.htmlspecialchars($r->title).'</td>';
		echo '<td>'.htmlspecialchars($r->max_hours_per_day).'</td>';
		echo '<td>'.htmlspecialchars($r->max_services_per_week).'</td>';
		echo '<td>'.htmlspecialchars($r->max_hours_per_week).'</td>';
		echo '<td>'.htmlspecialchars($r->max_hours_per_month).'</td>';
		echo '<td>'.$amount_users.'</td>';
		echo '<td class="wrapcontent">'
			.'<a class="button" href="?view=editRole&id='.$r->id.'" title="'.LANG['edit'].'"><img src="img/edit.svg"></a>'
			.'<form method="POST" onsubmit="return confirm('."'".LANG['really_remove_this_role']."'".')">'
			.'<input type="hidden" name="action" value="removeRole">'
			.'<input type="hidden" name="id" value="'.$r->id.'">'
			.'<button title="'.LANG['remove'].'"><img src="img/delete.svg"></button>'
			.'</form>'
			.'</td>';
		echo '</tr>';
	}
	?>
</table>
</div>
