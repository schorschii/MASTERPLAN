<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeRole' && !empty($_POST['id'])) {
		if($db->removeRole($_POST['id'])) {
			$info = "Die Rolle wurde gelöscht";
			$infoclass = "green";
		} else {
			$info = "Die Rolle konnte nicht gelöscht werden";
			$infoclass = "red";
		}
	}
}
?>

<div class='contentbox'>
<h2>Vorhandene Rollen (<?php echo count($db->getRoles()); ?>)</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='editRole'>
		<button><img src='img/add.svg'>&nbsp;Rolle</button>
	</form>
</div>

<table class="data rowhover">
	<tr>
		<th>Rollenbezeichnung</th><th>Std./Tag</th><th>Dienste/Woche</th><th>Std./Woche</th><th>Std./Monat</th><th>Anzahl Mitarbeiter</th><th>Aktion</th>
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
			.'<a class="button" href="?view=editRole&id='.$r->id.'"><img src="img/edit.svg"></a>'
			.'<form method="POST" onsubmit="return confirm('."'".'Möchten Sie diese Rolle wirklich löschen?'."'".')">'
			.'<input type="hidden" name="action" value="removeRole">'
			.'<input type="hidden" name="id" value="'.$r->id.'">'
			.'<button><img src="img/delete.svg"></button>'
			.'</form>'
			.'</td>';
		echo '</tr>';
	}
	?>
</table>
</div>
