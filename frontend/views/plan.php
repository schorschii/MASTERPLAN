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
			$info = LANG['end_date_before_start_date'];
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
		$info = LANG['end_date_before_start_date'];
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
	$info = LANG['timespan_too_big'];
	$infoclass = 'red';
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
		$info = LANG['no_read_rights_for_this_roster'];
		$infoclass = 'yellow';
	}
}

if(isset($_POST['action'])) {
	if($_POST['action'] == 'remove_all_assignments' && $dayViewStart != null && $dayViewEnd != null) {
		if(!$perm->isUserAdminForRoster($currentUser, $_POST['roster'])) {
			$info = LANG['no_admin_rights_for_this_roster'];
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
				$info = LANG['service_assignments_removed'];
				$infoclass = 'green';
			} else {
				$info = LANG['error'].': '.$db->getLastStatement()->error;
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
				$info = LANG['roster_released'];
				$infoclass = 'green';
			} else {
				$info = LANG['error'].': '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = LANG['no_admin_rights_for_this_roster'];
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
				$info = LANG['roster_release_revoked'];
				$infoclass = 'green';
			} else {
				$info = LANG['error'].': '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = LANG['no_admin_rights_for_this_roster'];
			$infoclass = 'red';
		}
	}
	elseif($_POST['action'] == 'autoplan_services' && $roster != null && $dayViewStart != null && $dayViewEnd != null) {

		if(!$perm->isUserAdminForRoster($currentUser, $_POST['roster'])) {
			$info = LANG['no_admin_rights_for_this_roster'];
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
								$info = LANG['error'].': '.$db->getLastStatement()->error;
								$infoclass = 'red';
							}
						} else {
							$info = LANG['roster_could_not_be_filled_automatically_missing_employees'];
							$infoclass = 'yellow';
							break;
						}
					}
				}
			}
			$successMsg = str_replace('%1', $deployedServices, LANG['services_filled_automatically']);
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
			$info = LANG['no_admin_rights_for_this_roster'];
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
				$info = str_replace('%1', $sentCount, LANG['invitations_sent_via_mail']);
				$infoclass = 'green';
			} else {
				$info = LANG['error'].': '.$db->getLastStatement()->error;
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
		$title = htmlspecialchars($s->title) . "\n" . count($assignments)."/".$s->employees." ".LANG['assigned'];
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
				echo '<button class="remove" title="'.LANG['remove_assignment'].'" onclick="removeAssignment('.$a->id.')">&#10005;</button>';
			echo htmlspecialchars($a->user_fullname)
				.'</div>';
		}
		/// service free slots ///
		for($i=count($assignments); $i<$s->employees; $i++) {
			echo '<div class="unassigneduser">'.LANG['vacant'].'</div>';
		}
		/// assigned resources ///
		$assignmentsResources = $db->getPlannedServiceResourcesByServiceAndDay($s->id, $day);
		foreach($assignmentsResources as $r) {
			$extraClass = '';
			if(color::isDarkBg($r->resource_color)) $extraClass = 'darkbg';
			echo '<div class="assignedresource '.$extraClass.'" style="background-color:'.htmlspecialchars($r->resource_color).'">';
			echo '<img class="embleme" src="'.htmlspecialchars($r->resource_icon).'">';
			if($perm->isUserAdminForRoster($currentUser, $roster_id)) {
				echo '<button class="remove" title="'.LANG['remove_assignment'].'" onclick="removeResourceAssignment('.$r->id.')">&#10005;</button>';
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
					echo '<button class="remove" title="'.LANG['remove_file'].'" onclick="removeFile('.$file->id.')">&#10005;</button>';
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
			echo "<button title='".LANG['assign_employee']."' onclick=\"addAssignment('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/user.svg\"></button>";
			echo "<button title='".LANG['assign_resource']."' onclick=\"addResource('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/resources.svg\"></button>";
			echo "<button title='".LANG['add_file']."' onclick=\"addFile('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/attach-file.svg\"></button>";
			echo "<button title='".LANG['add_note']."' onclick=\"addNote('".$s->id."','".htmlspecialchars($day)."')\"><img src=\"img/note.svg\"></button>";
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
	if(confirm('<?php echo LANG['really_remove_service_assignment']; ?>')) {
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
	if(confirm('<?php echo LANG['really_remove_resource_assignment']; ?>')) {
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
	if(confirm('<?php echo LANG['really_remove_file']; ?>')) {
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
	if(confirm('<?php echo LANG['confirm_start_autoplan']; ?>')) {
		showPopup();
		disableAllButtons();
		return true;
	} else {
		return false;
	}
}
function confirmSendMails() {
	if(confirm('<?php echo LANG['confirm_send_invitations']; ?>')) {
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
			<?php echo LANG['roster']; ?>:
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
			<label><input type="radio" name="timespan" value="week" <?php echo ($preselectTimespan=='week' ? 'checked' : ''); ?>><?php echo LANG['week']; ?>:</input></label>
			<input type="week" class="small" name="week" value="<?php echo htmlspecialchars($preselectWeek); ?>">
		</div>
		<div class="inlineblock">
			<label><input type="radio" name="timespan" value="flex" <?php echo ($preselectTimespan=='flex' ? 'checked' : ''); ?>><?php echo LANG['time_span']; ?>:</input></label>
			<input type="date" class="small" name="start" value="<?php echo htmlspecialchars($preselectStart); ?>">
			<input type="date" class="small" name="end" value="<?php echo htmlspecialchars($preselectEnd); ?>">
		</div>
		<button><img src="img/refresh.svg">&nbsp;<?php echo LANG['show']; ?></button>
	</form>
	<?php if($roster != null && $dayViewStart != null && $dayViewEnd != null) { ?>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="week">
		<input type="hidden" name="week" value="<?php echo date('Y').'-W'.date('W'); ?>">
		<button><img src="img/week-current.svg">&nbsp;<?php echo LANG['current_week']; ?></button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="week">
		<input type="hidden" name="week" value="<?php echo date('Y',strtotime('+1 week')).'-W'.date('W',strtotime('+1 week')); ?>">
		<button><img src="img/week-next.svg">&nbsp;<?php echo LANG['next_week']; ?></button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="flex">
		<input type="hidden" name="start" value="<?php echo date('Y-m-01'); ?>">
		<input type="hidden" name="end" value="<?php echo date('Y-m-t'); ?>">
		<button><img src="img/month-current.svg">&nbsp;<?php echo LANG['current_month']; ?></button>
	</form>
	<form method="GET" class="inlineblock">
		<input type="hidden" name="view" value="plan">
		<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
		<input type="hidden" name="timespan" value="flex">
		<input type="hidden" name="start" value="<?php echo date('Y-m-01',strtotime('+1 month')); ?>">
		<input type="hidden" name="end" value="<?php echo date('Y-m-t',strtotime('+1 month')); ?>">
		<button><img src="img/month-next.svg">&nbsp;<?php echo LANG['next_month']; ?></button>
	</form>
	<?php } ?>
	<?php if($roster == null || $dayViewStart == null || $dayViewEnd == null) { ?>
		<?php if($info != null) { ?>
			<div class="infobox margintop <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
		<?php } else { ?>
			<div class="infobox gray margintop"><?php echo LANG['please_select_timespan']; ?></div>
		<?php } ?>
	<?php } ?>
</div>

<?php if($roster != null && $dayViewStart != null && $dayViewEnd != null) { ?>
<div class="contentbox">
	<h2><?php echo htmlspecialchars($roster->title); ?>, <?php echo htmlspecialchars(strftime(DATE_FORMAT,$dayViewStart)." - ".strftime(DATE_FORMAT,$dayViewEnd)); ?></h2>

	<?php if($perm->isUserAdminForRoster($currentUser, $roster->id)) { ?>
	<?php if(!$lic->licenseValid) echo "<div class='infobox yellow'>".LANG['features_disabled_invalid_license']."</div>"; ?>
	<div class="toolbar marginbottom">
		<form method="POST" class="inlineblock" onsubmit="return confirmAutoplan()">
			<input type="hidden" name="action" value="autoplan_services">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button title="<?php echo LANG['fill_free_services_automatically']; ?>" <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img id="btnAutoplanImg" src="img/flash-auto.svg">&nbsp;<?php echo LANG['autoplan_services']; ?></button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirm('<?php echo LANG['really_remove_all_assigned_users']; ?>')">
			<input type="hidden" name="action" value="remove_all_assignments">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img src="img/cancel.svg">&nbsp;<?php echo LANG['remove_all_service_assignments']; ?></button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirm('<?php echo LANG['confirm_release_roster_now']; ?>')">
			<input type="hidden" name="action" value="release">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?> title="<?php echo LANG['release_roster_description']; ?>"><img src="img/check.svg">&nbsp;<?php echo LANG['release_roster']; ?></button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirm('<?php echo LANG['confirm_revoke_release_roster_now']; ?>')">
			<input type="hidden" name="action" value="remove_release">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?> title="<?php echo LANG['revoke_release_roster_description']; ?>"><img src="img/uncheck.svg">&nbsp;<?php echo LANG['revoke_release']; ?></button>
		</form>
		<form method="POST" class="inlineblock" onsubmit="return confirmSendMails()">
			<input type="hidden" name="action" value="send_ics_mail">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="start" value="<?php echo $preselectStart; ?>">
			<input type="hidden" name="end" value="<?php echo $preselectEnd; ?>">
			<button title="<?php echo LANG['send_invitations_description']; ?>" <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img id="btnSendMailsImg" src="img/calendar.svg">&nbsp;<?php echo LANG['send_invitations']; ?></button>
		</form>
		<form method="GET" class="inlineblock" action="export.php" target="_blank">
			<input type="hidden" name="export" value="plan">
			<input type="hidden" name="type" value="pdf">
			<input type="hidden" name="roster" value="<?php echo $roster->id; ?>">
			<input type="hidden" name="week" value="<?php echo date('Y',$dayViewStart).'-W'.date('W',$dayViewStart); ?>">
			<button <?php if(!$lic->licenseValid) echo " disabled='true'"; ?>><img id="btnSendMailsImg" src="img/export.svg">&nbsp;<?php echo LANG['pdf_week_view']; ?></button>
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
					<?php if(count($db->getReleasedPlansByRosterAndDay($roster->id, date('Y-m-d', $i))) > 0) echo '<img class="released" title="'.LANG['released'].'" src="img/check.svg">'; ?>
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
				<b><?php echo LANG['please_wait']; ?></b>
			</p>
			<p>
				<?php echo LANG['autoplan_in_progress_description']; ?>
			</p>
			<p class="hint">
				<?php echo LANG['autoplan_in_progress_description2']; ?>
			</p>
		</div>
	</div>
</div>
<?php } ?>
