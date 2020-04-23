<?php
$info = null;
$infoclass = null;

$plan = new plan($db);

// rights check
if(!isset($currentUser)) die();

// default week/day selection
$preselectRoster = 0;
$preselectTimespan = 'week';
$preselectWeek = date('Y', strtotime('+1 week')).'-W'.date('W', strtotime('+1 week'));
$preselectStart = date('Y-m-d', strtotime('+0 day'));
$preselectEnd = date('Y-m-d', strtotime('+6 day'));

// view start and end day in unix time (seconds integer)
$dayViewStart = null;
$dayViewEnd = null;

// parse input
if(isset($_SESSION['last_roster'])) {
	$preselectRoster = $_SESSION['last_roster'];
}
if(isset($_GET['timespan'])) {
	$preselectTimespan = $_GET['timespan'];
}
if(isset($_GET['timespan'])) {
	// week selection
	if($_GET['timespan'] == 'week' && isset($_GET['week'])) {
		$preselectWeek = $_GET['week'];
		$preselectStart = date('Y-m-d', strtotime($preselectWeek));
		$preselectEnd = date('Y-m-d', strtotime($preselectWeek." +6 day"));
		$dayViewStart = strtotime($preselectStart);
		$dayViewEnd = strtotime($preselectEnd);
	}
	// flex selection
	elseif($_GET['timespan'] == 'flex' && isset($_GET['start']) && isset($_GET['end'])) {
		if(strtotime($_GET['start']) > strtotime($_GET['end'])) {
			$info = 'Das Enddatum liegt vor dem Startdatum';
			$infoclass = 'red';
		} else {
			$preselectStart = $_GET['start'];
			$preselectEnd = $_GET['end'];
			$preselectWeek = date('Y', strtotime($preselectStart)).'-W'.date('W', strtotime($preselectStart));
			$dayViewStart = strtotime($preselectStart.' UTC');
			$dayViewEnd = strtotime($preselectEnd.' UTC');
		}
	}
}
// may be overwritten by POST data when user executed an action
if(isset($_POST['start']) && isset($_POST['end'])) {
	if(strtotime($_POST['start']) > strtotime($_POST['end'])) {
		$info = 'Das Enddatum liegt vor dem Startdatum';
		$infoclass = 'red';
	} else {
		$preselectStart = $_POST['start'];
		$preselectEnd = $_POST['end'];
		$preselectWeek = date('Y', strtotime($preselectStart)).'-W'.date('W', strtotime($preselectStart));
		$dayViewStart = strtotime($preselectStart.' UTC');
		$dayViewEnd = strtotime($preselectEnd.' UTC');
	}
}

// check max day limit
$counter = 0;
for($i = $dayViewStart; $i <= $dayViewEnd; $i = $i + 86400) {
	$counter ++;
}
if($counter > 31) {
	$dayViewStart = null;
	$dayViewEnd = null;
	$info = "Zeitraum zu groß";
	$infoclass = "red";
}

$roster = null;
if(isset($_GET['roster'])) {
	// check user rights
	if($perm->isUserAdminForRoster($currentUser, $_GET['roster'])
	|| $perm->isUserAssignedToRoster($currentUser, $_GET['roster'])) {
		$roster = $db->getRoster($_GET['roster']);
		$preselectRoster = $roster->id;
		$_SESSION['last_roster'] = $roster->id;
	} else {
		$info = 'Sie besitzen keine Leserechte für den angeforderten Dienstplan';
		$infoclass = 'yellow';
	}
}

if(isset($_POST['action'])) {
	if($_POST['action'] == 'remove_all_assignments' && $dayViewStart != null && $dayViewEnd != null) {
		if(!$perm->isUserAdminForRoster($currentUser, $_POST['roster'])) {
			$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
			$infoclass = 'red';
		} else {
			$success = true;
			// for each day of week
			for($i = $dayViewStart; $i <= $dayViewEnd; $i = $i + 86400) {
				foreach($db->getPlannedServicesWithUserByRosterAndDay($_POST['roster'], date('Y-m-d', $i)) as $ps) {
					$success = $plan->removeAssignment($ps->id);
				}
			}
			if($success) {
				$info = 'Dienstzuweisungen wurden entfernt';
				$infoclass = 'green';
			} else {
				$info = 'Dienstzuweisungen konnten nicht entfernt werden';
				$infoclass = 'red';
			}
		}
	}
	elseif($_POST['action'] == 'release' && $dayViewStart != null && $dayViewEnd != null) {
		if($perm->isUserAdminForRoster($currentUser, $_POST['roster'])) {
			$error = false;
			for($i = $dayViewStart; $i <= $dayViewEnd; $i = $i + 86400) {
				if(!$db->updateReleasedPlan(null, $_POST['roster'], date('Y-m-d', $i))) {
					$error = true;
					break;
				}
			}
			if(!$error) {
				$info = 'Dienstplan wurde freigegeben';
				$infoclass = 'green';
			} else {
				$info = 'Dienstplan konnte nicht freigegeben werden: '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
			$infoclass = 'red';
		}
	}
	elseif($_POST['action'] == 'remove_release' && $dayViewStart != null && $dayViewEnd != null) {
		if($perm->isUserAdminForRoster($currentUser, $_POST['roster'])) {
			$error = false;
			for($i = $dayViewStart; $i <= $dayViewEnd; $i = $i + 86400) {
				if(!$db->removeReleasedPlan($_POST['roster'], date('Y-m-d', $i))) {
					$error = true;
					break;
				}
			}
			if(!$error) {
				$info = 'Dienstplanfreigabe wurde aufgehoben';
				$infoclass = 'green';
			} else {
				$info = 'Dienstplanfreigabe konnte nicht aufgehoben werden: '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
			$infoclass = 'red';
		}
	}
	elseif($_POST['action'] == 'autoplan_services' && $roster != null && $dayViewStart != null && $dayViewEnd != null) {

		if(!$perm->isUserAdminForRoster($currentUser, $_POST['roster'])) {
			$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
			$infoclass = 'red';
		} else {
			$aplan = new autoplan($db);
			$deployedServices = 0;
			$weekStr = date("Y", $dayViewStart)."-W".date("W", $dayViewStart);
			// for each day of week
			for($i = $dayViewStart; $i <= $dayViewEnd; $i = $i + 86400) {
				$strDay = date('Y-m-d', $i);
				// for each service of day
				foreach($plan->getConsolidatedServicesByRosterAndDay($_POST['roster'], $strDay) as $s) {
					$assignments = $db->getPlannedServicesWithUserByRosterAndServiceAndDay($_POST['roster'], $s->id, $strDay);
					// for each vacant service slot
					for($n=count($assignments); $n<$s->employees; $n++) {
						// deploy vacant service to user with most free capacity
						$random = false;
						if($roster->autoplan_logic == 1) { $random = true; }
						$users = $aplan->getPotentialUsers($_POST['roster'], $weekStr, $strDay, $s->id, $random);
						#var_dump($users); die();
						if(isset($users[0])) {
							if($db->updatePlannedService(null, $strDay, $s->id, $users[0]['id'])) {
								$deployedServices ++;
							} else {
								$info = 'Benutzer konnte nicht zugeordnet werden: '.$db->getLastStatement()->error;
								$infoclass = 'red';
							}
						} else {
							$info = 'Der Dienstplan konnte aufgrund fehlender Personalressourcen nicht vollständig besetzt werden';
							$infoclass = 'yellow';
							break;
						}
					}
				}
			}
			$successMsg = $deployedServices.' Dienst(e) wurden automatisch besetzt';
			if($info == null) {
				$info = $successMsg;
				$infoclass = 'green';
			} else {
				$info = $info.' ('.$successMsg.')';
			}
		}

	}
	elseif($_POST['action'] == 'send_ics_mail' && $dayViewStart != null && $dayViewEnd != null) {
		if(!$perm->isUserAdminForRoster($currentUser, $_POST['roster'])) {
			$info = 'Sie besitzen keine Admin-Rechte für diesen Dienstplan';
			$infoclass = 'red';
		} else {

			$error = false;
			$sentCount = 0;
			for($i = $dayViewStart; $i <= $dayViewEnd; $i = $i + 86400) {
				$strDay = date('Y-m-d', $i);
				foreach($plan->getConsolidatedServicesByRosterAndDay($_POST['roster'], $strDay) as $s) {
					foreach($db->getPlannedServicesWithUserByRosterAndServiceAndDay($_POST['roster'], $s->id, $strDay) as $ps) {
						if($ps->icsmail_sent != null && $ps->icsmail_sent != 0) continue;
						if(!tools::isValidEmail($db->getUser($ps->user_id)->email)) continue;
						if($plan->sendIcsMail($ps, false)) {
							$sentCount ++;
						} else {
							$error = true;
						}
					}
				}
			}
			if(!$error) {
				$info = $sentCount.' Termineinladung(en) wurde(n) via E-Mail gesendet';
				$infoclass = 'green';
			} else {
				$info = 'Termineinladungen konnten nicht gesendet werden: '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}

		}
	}
}

function echoPlannedServices($roster_id, $wd, $day) {
	global $db;
	global $perm;
	global $plan;
	global $currentUser;
	foreach($plan->getConsolidatedServicesByRosterAndDay($roster_id, $day) as $s) {
		/// service meta data ///
		$assignments = $db->getPlannedServicesWithUserByRosterAndServiceAndDay($roster_id, $s->id, $day);
		$extraClass = '';
		if(color::isDarkBg($s->color)) $extraClass = 'darkbg';
		$extraClassStatusbar = '';
		if(count($assignments) > $s->employees) $extraClassStatusbar = 'overload';
		$statusBarPercent = round(count($assignments)*100/$s->employees);
		$title = htmlspecialchars($s->title) . "\n" . count($assignments)."/".$s->employees." belegt";
		echo '<div class="service '.$extraClass.'" title="'.$title.'" style="background-color:'.htmlspecialchars($s->color).'">';
		echo '<div class="statusbar '.$extraClassStatusbar.'" style="width:'.$statusBarPercent.'%"></div>';
		echo '<div class="title">'.htmlspecialchars($s->shortname).'</div>';
		echo '<div class="subtitle">'.htmlspecialchars($s->start).'-'.htmlspecialchars($s->end).'</div>';
		/// person assignments ///
		foreach($assignments as $a) {
			$extraClass = '';
			if(color::isDarkBg($a->user_color)) $extraClass = 'darkbg';
			echo '<div class="assigneduser '.$extraClass.'" style="background-color:'.htmlspecialchars($a->user_color).'">';
			echo '<img class="embleme" src="img/user.svg">';
			if($a->icsmail_sent != null && $a->icsmail_sent != 0)
				echo '<img class="icsmail_sent" src="img/email.svg">';
			if($perm->isUserAdminForRoster($currentUser, $roster_id))
				echo '<button class="remove" title="Zuweisung entfernen" onclick="removeAssignment('.$a->id.')">&#10005;</button>';
			echo htmlspecialchars($a->user_fullname)
				.'</div>';
		}
		/// service free slots ///
		for($i=count($assignments); $i<$s->employees; $i++) {
			echo '<div class="unassigneduser">'.'vakant'.'</div>';
		}
		/// assigned resources ///
		$assignmentsResources = $db->getPlannedServiceResourcesByServiceAndDay($s->id, $day);
		foreach($assignmentsResources as $r) {
			$extraClass = '';
			if(color::isDarkBg($r->resource_color)) $extraClass = 'darkbg';
			echo '<div class="assignedresource '.$extraClass.'" style="background-color:'.htmlspecialchars($r->resource_color).'">';
			echo '<img class="embleme" src="'.htmlspecialchars($r->resource_icon).'">';
			if($perm->isUserAdminForRoster($currentUser, $roster_id)) {
				echo '<button class="remove" title="Zuweisung entfernen" onclick="removeResourceAssignment('.$r->id.')">&#10005;</button>';
			}
			echo htmlspecialchars($r->resource_title);
			echo '</div>';
		}
		/// attached files ///
		$files = $db->getPlannedServiceFilesByServiceIdAndDay($s->id, $day);
		if(isset($files[0])) {
			foreach($files as $file) {
				echo '<div class="assignedfile">';
				echo '<a href="fileprovider.php?type=servicefile&id='.$file->id.'" target="_blank" title="'.htmlspecialchars($file->title).'">'
					.'<img class="embleme" src="img/file.svg">&nbsp;'
					.nl2br(htmlspecialchars(tools::shortText($file->title,10)))
					.'</a>';
				if($perm->isUserAdminForRoster($currentUser, $roster_id)) {
					echo '<button class="remove" title="Datei entfernen" onclick="removeFile('.$file->id.')">&#10005;</button>';
				}
				echo '</div>';
			}
		}
		/// service note ///
		$notes = $db->getPlannedServiceNotes($s->id, $day);
		if(isset($notes[0]))
			echo '<div class="servicenote" title="'.htmlspecialchars($notes[0]->note).'">'.nl2br(htmlspecialchars(tools::shortText($notes[0]->note))).'</div>';
		/// service admin buttons ///
		if($perm->isUserAdminForRoster($currentUser, $roster_id)) {
			echo "<div class='servicetoolbar'>";
			echo "<button title='Mitarbeiter zuweisen' onclick=\"addAssignment('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/user.svg\"></button>";
			echo "<button title='Ressource zuweisen' onclick=\"addResource('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/resources.svg\"></button>";
			echo "<button title='Datei anhängen' onclick=\"addFile('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/attach-file.svg\"></button>";
			echo "<button title='Notiz hinzufügen' onclick=\"addNote('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/note.svg\"></button>";
			echo "</div>";
		}
		echo '</div>';
	}
}
?>

<script>
<?php if(isset($_SESSION['scroll'])) { ?>
	document.addEventListener('DOMContentLoaded', function () {
		setTimeout(function() {
			document.documentElement.scrollTop = <?php echo intval($_SESSION['scroll']); ?>;
		}, 20);
	});
<?php } ?>
function reloadWithoutPost() {
	//location.reload();
	var body = urlencodeObject({
		'scroll' : document.documentElement.scrollTop,
		'action' : 'set_scroll'
	});
	ajaxRequestPost('ajaxHandler.php', body, null, function(responseText) {
		window.location.replace( window.location.href );
	});
}
function addAssignment(id, day) {
	var win = PopupCenter(
		'index.php?nomenubar=1&view=deployUser&service='+encodeURIComponent(id)+'&day='+encodeURIComponent(day),
		'MASTERPLAN'
	);
	var timer = setInterval(function() {
		if(win.closed) {
			clearInterval(timer);
			reloadWithoutPost();
		}
	}, 250);
}
function addResource(id, day) {
	var win = PopupCenter(
		'index.php?nomenubar=1&view=deployResource&service='+encodeURIComponent(id)+'&day='+encodeURIComponent(day),
		'MASTERPLAN'
	);
	var timer = setInterval(function() {
		if(win.closed) {
			clearInterval(timer);
			reloadWithoutPost();
		}
	}, 250);
}
function addFile(id, day) {
	var win = PopupCenter(
		'index.php?nomenubar=1&view=deployFile&service='+encodeURIComponent(id)+'&day='+encodeURIComponent(day),
		'MASTERPLAN'
	);
	var timer = setInterval(function() {
		if(win.closed) {
			clearInterval(timer);
			reloadWithoutPost();
		}
	}, 250);
}
function addNote(id, day) {
	var win = PopupCenter(
		'index.php?nomenubar=1&view=deployNote&service='+encodeURIComponent(id)+'&day='+encodeURIComponent(day),
		'MASTERPLAN'
	);
	var timer = setInterval(function() {
		if(win.closed) {
			clearInterval(timer);
			reloadWithoutPost();
		}
	}, 250);
}
function removeAssignment(id) {
	if(confirm('Dienstzuweisung wirklich löschen? Wenn bereits eine Termineinladung versendet wurde, wird automatisch eine Terminabsage gesendet.')) {
		var body = urlencodeObject({
			'id' : id,
			'action' : 'remove_assignment'
		});
		ajaxRequestPost('ajaxHandler.php', body, null, function(responseText) {
			reloadWithoutPost();
		});
	}
}
function removeResourceAssignment(id) {
	if(confirm('Ressourcenzuweisung wirklich löschen?')) {
		var body = urlencodeObject({
			'id' : id,
			'action' : 'remove_resource'
		});
		ajaxRequestPost('ajaxHandler.php', body, null, function(responseText) {
			reloadWithoutPost();
		});
	}
}
function removeFile(id) {
	if(confirm('Datei wirklich löschen?')) {
		var body = urlencodeObject({
			'id' : id,
			'action' : 'remove_file'
		});
		ajaxRequestPost('ajaxHandler.php', body, null, function(responseText) {
			reloadWithoutPost();
		});
	}
}
function disableAllButtons() {
	for(let btn of document.getElementsByTagName('button')) {
		btn.disabled = true;
	}
}
function confirmAutoplan() {
	if(confirm('Automatische Dienstvergabe starten?')) {
		showPopup();
		disableAllButtons();
		return true;
	} else {
		return false;
	}
}
function confirmSendMails() {
	if(confirm('Termineinladungen jetzt senden?')) {
		showPopup();
		disableAllButtons();
		return true;
	} else {
		return false;
	}
}
function showPopup() {
	popupBg.style.display = 'flex';
}
</script>

<div class="contentbox toolbar">
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<div class="inlineblock">
			Dienstplan:
			<select name="roster" autofocus="true">
				<?php foreach($db->getRosters() as $r) {
					if(!$perm->isUserAdminForRoster($currentUser, $r->id)
					&& !$perm->isUserAssignedToRoster($currentUser, $r->id))
						continue;
					echo "<option ".htmlinput::selectIf($r->id,$preselectRoster).">".htmlspecialchars($r->title)."</option>";
				} ?>
			</select>
		</div>
		<div class="inlineblock">
			<label><input type="radio" name="timespan" value="week" <?php echo ($preselectTimespan=='week' ? 'checked' : ''); ?>>Woche:</input></label>
			<input type="week" class="small" name="week" value="<?php echo htmlspecialchars($preselectWeek); ?>">
		</div>
		<div class="inlineblock">
			<label><input type="radio" name="timespan" value="flex" <?php echo ($preselectTimespan=='flex' ? 'checked' : ''); ?>>Zeitspanne:</input></label>
			<input type="date" class="small" name="start" value="<?php echo htmlspecialchars($preselectStart); ?>">
			<input type="date" class="small" name="end" value="<?php echo htmlspecialchars($preselectEnd); ?>">
		</div>
		<button><img src="img/refresh.svg">&nbsp;Anzeigen</button>
	</form>
	<?php if($roster != null && $dayViewStart != null && $dayViewEnd != null) { ?>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="week">
		<input type="hidden" name="week" value="<?php echo date('Y').'-W'.date('W'); ?>">
		<button><img src="img/week-current.svg">&nbsp;Aktuelle Woche</button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="week">
		<input type="hidden" name="week" value="<?php echo date('Y',strtotime('+1 week')).'-W'.date('W',strtotime('+1 week')); ?>">
		<button><img src="img/week-next.svg">&nbsp;Kommende Woche</button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="flex">
		<input type="hidden" name="start" value="<?php echo date('Y-m-01'); ?>">
		<input type="hidden" name="end" value="<?php echo date('Y-m-t'); ?>">
		<button><img src="img/month-current.svg">&nbsp;Aktueller Monat</button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="flex">
		<input type="hidden" name="start" value="<?php echo date('Y-m-01',strtotime('+1 month')); ?>">
		<input type="hidden" name="end" value="<?php echo date('Y-m-t',strtotime('+1 month')); ?>">
		<button><img src="img/month-next.svg">&nbsp;Kommender Monat</button>
	</form>
	<?php } ?>
	<?php if($roster == null || $dayViewStart == null || $dayViewEnd == null) { ?>
		<?php if($info != null) { ?>
			<div class="infobox margintop <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
		<?php } else { ?>
			<div class="infobox gray margintop">Bitte wählen Sie eine Zeitspanne aus</div>
		<?php } ?>
	<?php } ?>
</div>

<?php if($roster != null && $dayViewStart != null && $dayViewEnd != null) { ?>
<div class="contentbox">
	<h2><?php echo htmlspecialchars($roster->title); ?>, <?php echo htmlspecialchars(strftime(DATE_FORMAT,$dayViewStart)." - ".strftime(DATE_FORMAT,$dayViewEnd)); ?></h2>

	<?php if($perm->isUserAdminForRoster($currentUser, $roster->id)) { ?>
	<?php if(!$lic->licenseValid) echo "<div class='infobox yellow'>Aufgrund Ihrer ungültigen Lizenz wurden einige Funktionen deaktiviert.</div>"; ?>
	<div class="toolbar marginbottom">
		<form method="POST" class="inlineblock" onsubmit="return confirmAutoplan()">
			<input type="hidden" name="action" value="autoplan_services">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button title="Freie Dienste automatisch befüllen" <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img id="btnAutoplanImg" src="img/flash-auto.svg">&nbsp;Dienste automatisch besetzen</button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirm('Möchten Sie wirklich alle zugewiesenen Benutzer entfernen?')">
			<input type="hidden" name="action" value="remove_all_assignments">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img src="img/cancel.svg">&nbsp;Alle Dienstzuweisungen entfernen</button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirm('Dienstplan jetzt freigeben?')">
			<input type="hidden" name="action" value="release">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?> title="Dienstplan für alle angezeigten Dienste freigeben"><img src="img/check.svg">&nbsp;Dienstplan freigeben</button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirm('Dienstplanfreigabe wirklich zurücknehmen?')">
			<input type="hidden" name="action" value="remove_release">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?> title="Freigabe für alle angezeigten Dienste zurücknehmen"><img src="img/uncheck.svg">&nbsp;Freigabe zurücknehmen</button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirmSendMails()">
			<input type="hidden" name="action" value="send_ics_mail">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button title="Termineinladungen via E-Mail senden" <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img id="btnSendMailsImg" src="img/calendar.svg">&nbsp;Termineinladungen senden</button>
		</form>
		<form method="GET" class="inlineblock" action="export.php" target="_blank">
			<input type="hidden" name="export" value="plan">
			<input type="hidden" name="type" value="pdf">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="week" value="<?php echo date('Y',$dayViewStart).'-W'.date('W',$dayViewStart); ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img id="btnSendMailsImg" src="img/export.svg">&nbsp;PDF (Wochenansicht)</button>
		</form>
	</div>
	<?php } ?>

	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>

	<?php
	$daysToShowParts = [];
	$partCounter = 0;
	for($i = $dayViewStart; $i <= $dayViewEnd; $i = $i + 86400) {
		$daysToShowParts[$partCounter][] = $i;
		if(count($daysToShowParts[$partCounter]) == 7) {
			$partCounter ++;
		}
	}
	?>

	<table class="data plan">
		<?php
		$counter = 0;
		foreach($daysToShowParts as $part) {
		?>
		<tr>
			<?php foreach($part as $i) { ?>
				<th class="<?php if(date('Y-m-d')==date('Y-m-d',$i)) echo 'mark'; ?>">
					<?php echo strftime('%A', $i); ?>
					<?php if(count($db->getReleasedPlansByRosterAndDay($roster->id, date('Y-m-d', $i))) > 0) echo '<img class="released" title="freigegeben" src="img/check.svg">'; ?>
				</th>
			<?php } ?>
		</tr>
		<tr>
			<?php foreach($part as $i) { ?>
				<th class="<?php if(date('Y-m-d')==date('Y-m-d',$i)) echo 'mark'; ?>"><?php echo strftime(DATE_FORMAT, $i); ?></th>
			<?php } ?>
		</tr>
		<tr>
			<?php foreach($part as $i) { ?>
				<td class="<?php if(date('Y-m-d')==date('Y-m-d',$i)) echo 'mark'; ?>">
					<?php echoPlannedServices($roster->id, 1, date('Y-m-d', $i)); ?>
				</td>
			<?php } ?>
		</tr>
		<?php if(isset($daysToShowParts[$counter+1])) { ?>
			<tr class="topborder nopadding"><td colspan="<?php echo count($part); ?>"></td></tr>
		<?php } ?>
		<?php
		$counter ++;
		}
		?>
	</table>

	<div id="popupBg" style="display:none">
		<div id="popupContent">
			<p>
				<img src="img/loader.svg">
				<br>
				<b>Bitte warten...</b>
			</p>
			<p>
				Je nach Anzahl der zu planenden Tage, Dienste und Mitarbeiter kann dieser Vorgang mehrere Minuten dauern.
			</p>
			<p class="hint">
				Es wird empfohlen immer nur eine Woche automatisch zu planen.
			</p>
		</div>
	</div>
</div>
<?php } ?>
