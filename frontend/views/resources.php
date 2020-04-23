<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeResource' && !empty($_POST['id'])) {
		if($db->removeResource($_POST['id'])) {
			$info = "Die Ressource wurde gelöscht";
			$infoclass = "green";
		} else {
			$info = "Die Ressource konnte nicht gelöscht werden";
			$infoclass = "red";
		}
	}
}
?>

<div class='contentbox'>
<h2>Vorhandene Ressourcen (<?php echo count($db->getResources()); ?>)</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='editResource'>
		<button><img src='img/add.svg'>&nbsp;Ressource</button>
	</form>
</div>

<table class="data rowhover">
	<tr>
		<th>Icon/Farbe</th><th>Typ</th><th>Bezeichnung</th><th>Beschreibung</th><th>Aktion</th>
	</tr>
	<?php
	foreach($db->getResources() as $r) {
		echo '<tr>';
		echo '<td>'
			.(($r->icon != null && $r->icon != '') ? ' <img src="'.htmlspecialchars($r->icon).'">' : '')
			.' <span class="colorpreview" style="background-color:'.htmlspecialchars($r->color).'"></span>'
			.'</td>';
		echo '<td>'.htmlspecialchars($r->type).'</td>';
		echo '<td>'.htmlspecialchars($r->title).'</td>';
		echo '<td>'.htmlspecialchars(tools::shortText($r->description)).'</td>';
		echo '<td class="wrapcontent">'
			.'<a class="button" href="?view=editResource&id='.$r->id.'"><img src="img/edit.svg"></a>'
			.'<form method="POST" onsubmit="return confirm('."'".'Möchten Sie diese Ressource wirklich löschen?'."'".')">'
			.'<input type="hidden" name="action" value="removeResource">'
			.'<input type="hidden" name="id" value="'.$r->id.'">'
			.'<button><img src="img/delete.svg"></button>'
			.'</form>'
			.'</td>';
		echo '</tr>';
	}
	?>
</table>
</div>
