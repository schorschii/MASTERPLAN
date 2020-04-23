<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeHoliday' && !empty($_POST['id'])) {
		if($db->removeHoliday($_POST['id'])) {
			$info = "Der Schließtag wurde gelöscht";
			$infoclass = "green";
		} else {
			$info = "Der Scheißtag konnte nicht gelöscht werden";
			$infoclass = "red";
		}
	}
}
?>

<div class='contentbox'>
<h2>Vorhandene Schließtage (<?php echo count($db->getHolidays()); ?>)</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='editHoliday'>
		<button><img src='img/add.svg'>&nbsp;Schließtag</button>
	</form>
</div>

<table class="data rowhover">
	<tr>
		<th>Bezeichnung</th><th>Tag</th><th>Betroffene Dienste</th><th>Aktion</th>
	</tr>
	<?php
	foreach($db->getHolidays() as $h) {
		$service_title = '<b>Alle</b>';
		if($h->service_id != null) {
			$service_title = htmlspecialchars($h->service_shortname." ".$h->service_title);
		}

		echo '<tr>';
		echo '<td>'.htmlspecialchars($h->title).'</td>';
		echo '<td>'.htmlspecialchars($h->day).'</td>';
		echo '<td>'.$service_title.'</td>';
		echo '<td class="wrapcontent">'
			.'<a class="button" href="?view=editHoliday&id='.$h->id.'"><img src="img/edit.svg"></a>'
			.'<form method="POST" onsubmit="return confirm('."'".'Möchten Sie diesen Schließtag wirklich löschen?'."'".')">'
			.'<input type="hidden" name="action" value="removeHoliday">'
			.'<input type="hidden" name="id" value="'.$h->id.'">'
			.'<button><img src="img/delete.svg"></button>'
			.'</form>'
			.'</td>';
		echo '</tr>';
	}
	?>
</table>
</div>
