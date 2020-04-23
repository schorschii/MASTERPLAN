<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeRoster' && !empty($_POST['id'])) {
		if($db->removeRoster($_POST['id'])) {
			$info = "Der Dienstplan und zugehörige Dienste wurden gelöscht";
			$infoclass = "green";
		} else {
			$info = "Der Dienstplan konnte nicht gelöscht werden";
			$infoclass = "red";
		}
	}
	elseif($_POST['action'] == 'removeService' && !empty($_POST['id'])) {
		if($db->removeService($_POST['id'])) {
			$info = "Der Dienst wurde gelöscht";
			$infoclass = "green";
		} else {
			$info = "Der Dienst konnte nicht gelöscht werden";
			$infoclass = "red";
		}
	}
}
?>

<script>
function copyServices() {
	var service_ids = [];
	var services = document.getElementsByClassName('service');
	for(i=0; i<services.length; i++) {
		if(services[i].checked)
			service_ids.push(services[i].value);
	}
	var page = 'index.php?view=copyServices';
	var param = urlencodeObject({
		'services' : service_ids.join(',')
	});
	window.location.replace( page + '&' + param );
}
</script>

<div class='contentbox'>
<h2>Vorhandene Dienstpläne und Dienste</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="editRoster">
		<button title='Neuer Dienstplan'><img src='img/add.svg'>&nbsp;Dienstplan</button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="editService">
		<button title='Neuer Dienst'><img src='img/add.svg'>&nbsp;Dienst</button>
	</form>
	<form method="GET" class="inlineblock">
		<button type='button' onclick='copyServices()' class="inlineblock"><img src='img/copy.svg'>&nbsp;Markierte Dienste kopieren</button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="holidays">
		<button title='Schließtage/Feiertage definieren'><img src='img/holiday.svg'>&nbsp;Schließtage definieren</button>
	</form>
</div>

<table class='data rowhover'>
<?php
foreach($db->getRosters() as $r) {
	echo "<tr>"
		. "<th colspan='4'>"
		. "<h3 class='inlineblock'>".htmlspecialchars($r->title)."</h3>"
		. "<form method='GET' action='index.php' class='marginleft'>"
		.  "<input type='hidden' name='view' value='assignUsersToRoster'>"
		.  "<input type='hidden' name='roster' value='".$r->id."'>"
		.  "<button title='Benutzer zuweisen'><img src='img/users.svg'></button>"
		. "</form>"
		. "</th>"
		. "<th>Start</th>"
		. "<th>Ende</th>"
		. "<th>Gültig ab</th>"
		. "<th>Gültig bis</th>"
		. "<th class='wrapcontent'>"
		. "<a class='button' href='?view=editRoster&id=".$r->id."'><img src='img/edit.svg'></a>"
		. "<form method='POST' onsubmit='return confirm(\"Möchten Sie den kompletten Dienstplan einschließlich dessen Dienste löschen?\")'>"
		. "<input type='hidden' name='action' value='removeRoster'>"
		. "<input type='hidden' name='id' value='".$r->id."'>"
		. "<button><img src='img/delete.svg'></button>"
		. "</form>"
		. "</th>"
		. "</tr>";
	foreach($db->getServicesFromRoster($r->id) as $s) {
		$wds = [];
		if($s->wd1) $wds[] = "Mo";
		if($s->wd2) $wds[] = "Di";
		if($s->wd3) $wds[] = "Mi";
		if($s->wd4) $wds[] = "Do";
		if($s->wd5) $wds[] = "Fr";
		if($s->wd6) $wds[] = "Sa";
		if($s->wd7) $wds[] = "So";
		echo "<tr>";
		echo "<td>"
			. "<input type='checkbox' class='service' name='services[]' value='".$s->id."'>"
			. "<span class='colorpreview' style='background-color:".htmlspecialchars($s->color)."'></span>"
			."</td>";
		echo "<td>".htmlspecialchars($s->shortname)."</td>";
		echo "<td>".htmlspecialchars($s->title)."</td>";
		echo "<td>".implode(', ',$wds)."</td>";
		echo "<td>".htmlspecialchars($s->start)."</td>";
		echo "<td>".htmlspecialchars($s->end)."</td>";
		echo "<td>".htmlspecialchars(strftime(DATE_FORMAT,strtotime($s->date_start)))."</td>";
		echo "<td>".htmlspecialchars(strftime(DATE_FORMAT,strtotime($s->date_end)))."</td>";
		echo "<td class='wrapcontent'>"
			. "<a class='button' href='?view=editService&id=".$s->id."'><img src='img/edit.svg'></a>"
			. "<form method='POST' onsubmit='return confirm(\"Möchten Sie diesen Dienst löschen?\")'>"
			. "<input type='hidden' name='action' value='removeService'>"
			. "<input type='hidden' name='id' value='".$s->id."'>"
			. "<button><img src='img/delete.svg'></button>"
			. "</form>"
			. "</td>";
		echo "</tr>";
	}
}
?>
</table>
</div>
