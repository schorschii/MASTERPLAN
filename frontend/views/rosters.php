<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeRoster' && !empty($_POST['id'])) {
		if($db->removeRoster($_POST['id'])) {
			$info = LANG['roster_and_services_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
	elseif($_POST['action'] == 'removeService' && !empty($_POST['id'])) {
		if($db->removeService($_POST['id'])) {
			$info = LANG['service_removed'];
			$infoclass = "green";
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
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
<h2><?php echo LANG['existing_rosters_and_services']; ?></h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="editRoster">
		<button><img src='img/add.svg'>&nbsp;<?php echo LANG['roster']; ?></button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="editService">
		<button><img src='img/add.svg'>&nbsp;<?php echo LANG['service']; ?></button>
	</form>
	<form method="GET" class="inlineblock">
		<button type='button' onclick='copyServices()' class="inlineblock"><img src='img/copy.svg'>&nbsp;<?php echo LANG['copy_selected_services']; ?></button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="holidays">
		<button><img src='img/holiday.svg'>&nbsp;<?php echo LANG['define_holidays']; ?></button>
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
		.  "<button title='".LANG['assign_employee']."'><img src='img/users.svg'></button>"
		. "</form>"
		. "</th>"
		. "<th>".LANG['begin']."</th>"
		. "<th>".LANG['end']."</th>"
		. "<th>".LANG['valid_from']."</th>"
		. "<th>".LANG['valid_to']."</th>"
		. "<th class='wrapcontent'>"
		. "<a class='button' href='?view=editRoster&id=".$r->id."' title='".LANG['edit']."'><img src='img/edit.svg'></a>"
		. "<form method='POST' onsubmit='return confirm(\"".LANG['confirm_remove_complete_roster_including_services']."\")'>"
		. "<input type='hidden' name='action' value='removeRoster'>"
		. "<input type='hidden' name='id' value='".$r->id."'>"
		. "<button title='".LANG['remove']."'><img src='img/delete.svg'></button>"
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
			. "<a class='button' href='?view=editService&id=".$s->id."' title='".LANG['edit']."'><img src='img/edit.svg'></a>"
			. "<form method='POST' onsubmit='return confirm(\"".LANG['confirm_remove_service']."\")'>"
			. "<input type='hidden' name='action' value='removeService'>"
			. "<input type='hidden' name='id' value='".$s->id."'>"
			. "<button title='".LANG['remove']."'><img src='img/delete.svg'></button>"
			. "</form>"
			. "</td>";
		echo "</tr>";
	}
}
?>
</table>
</div>
