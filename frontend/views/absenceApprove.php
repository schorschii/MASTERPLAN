<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

$adminRosters = $db->getUserRostersAdmin($currentUser->id);


if(isset($_POST['action'])) {
	if($_POST['action'] == 'approveAbsence1') {

		if(hasRights($_POST['id'])) {
			if($db->approveAbsence1($_POST['id'], 1, $currentUser->id)) {
				$info = LANG['absence_approved'];
				$infoclass = 'green';
				// send mail to employee
				if(boolval($db->getSetting('absence_mails'))) {
					$mailer = new mailer($db);
					$mailer->mailAbsenceApproved1($_POST['id']);
				}
			} else {
				$info = LANG['absence_could_not_be_approved'].' '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = LANG['missing_rights_to_approve_this_absence'];
			$infoclass = 'red';
		}

	}
	elseif($_POST['action'] == 'approveAbsence2') {

		if(hasRights($_POST['id']) && $currentUser->superadmin > 0) {
			if($db->approveAbsence2($_POST['id'], 1, $currentUser->id)) {
				$info = LANG['absence_confirmed'];
				$infoclass = 'green';
				// send mail to employee
				if(boolval($db->getSetting('absence_mails'))) {
					$mailer = new mailer($db);
					$mailer->mailAbsenceApproved2($_POST['id']);
				}
			} else {
				$info = LANG['absence_could_not_be_confirmed'].' '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = LANG['missing_rights_to_confirm_this_absence'];
			$infoclass = 'red';
		}

	}
	elseif($_POST['action'] == 'declineAbsence') {

		if(hasRights($_POST['id'])) {
			// send mail to employee
			if(boolval($db->getSetting('absence_mails'))) {
				$mailer = new mailer($db);
				$mailer->mailAbsenceDeclined($_POST['id']);
			}
			if($db->removeAbsence($_POST['id'])) {
				$info = LANG['absence_declined'];
				$infoclass = 'green';
			} else {
				$info = LANG['absence_could_not_be_declined'].' '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = LANG['missing_rights_to_decline_this_absence'];
			$infoclass = 'red';
		}

	}
}

function hasRights($absence_id) {
	global $db;
	global $adminRosters;
	global $currentUser;
	if($currentUser->superadmin > 0) return true;
	$absence = $db->getAbsence($absence_id);
	if($absence == null) return false;
	foreach($adminRosters as $ar) {
		$user = $db->getUser($absence->user_id);
		if($user != null) {
			foreach($db->getUserRosters($user->id) as $ur) {
				if($ur->roster_id == $ar->roster_id) {
					return true;
				}
			}
		}
	}
	return false;
}
function echoUnapprovedAbsences($roster_id) {
	global $db;
	global $currentUser;

	$users = [];
	if($roster_id == null) {
		$users = $db->getUsers();
	} else {
		$roster = $db->getRoster($roster_id);
		echo '<tr><th colspan="6"><div class="infobox gray">'.htmlspecialchars($roster->title).'</div></th></tr>';
		$users = $db->getUsersByRoster($roster_id);
	}

	foreach($users as $u) {
		foreach($db->getUnapprovedAbsencesByUser($u->id) as $a) {
			echo '<tr>';
			echo '<td>'; boxes::echoUser($u); echo '</td>';
			echo '<td>'.htmlspecialchars(strftime(DATE_FORMAT,strtotime($a->start))).'</td>';
			echo '<td>'.htmlspecialchars(strftime(DATE_FORMAT,strtotime($a->end))).'</td>';
			echo '<td title="'.htmlspecialchars($a->comment).'">'.htmlspecialchars(tools::shortText($a->comment)).'</td>';
			echo '<td></td>';
			echo '<td class="wrapcontent">';

			if($db->getSetting('absence_confirmation_required') == 1
			|| $db->getSetting('absence_confirmation_required') == 2) {
				echo "<form method=\"POST\" onsubmit=\"return confirm('".LANG['approve_absence_confirmation']."')\">"
					.'<input type="hidden" name="action" value="approveAbsence1">'
					.'<input type="hidden" name="id" value="'.$a->id.'">';
				if($a->approved1 > 0) {
					echo '<button disabled="true"><img src="img/ok1.svg">&nbsp;'.LANG['approved'].'</button>';
				} else {
					echo '<button><img src="img/ok1.svg">&nbsp;'.LANG['approve'].'</button>';
				}
				echo '</form>';
			}

			if($db->getSetting('absence_confirmation_required') == 2
			&& $currentUser->superadmin > 0) {
				echo "<form method=\"POST\" onsubmit=\"return confirm('".LANG['confirm_absence_confirmation']."')\">"
					.'<input type="hidden" name="action" value="approveAbsence2">'
					.'<input type="hidden" name="id" value="'.$a->id.'">';
				if($a->approved2 > 0) {
					echo '<button disabled="true"><img src="img/ok2.svg">&nbsp;'.LANG['confirmed'].'</button>';
				} else {
					echo '<button><img src="img/ok2.svg">&nbsp;'.LANG['confirm'].'</button>';
				}
				echo '</form>';
			}

			echo "<form method=\"POST\" onsubmit=\"return confirm('".LANG['decline_absence_confirmation']."')\">"
				.'<input type="hidden" name="action" value="declineAbsence">'
				.'<input type="hidden" name="id" value="'.$a->id.'">'
				.'<button><img src="img/cancel.svg">&nbsp;'.LANG['decline'].'</button>'
				.'</form>';
			echo '</td>';
			echo '</tr>';
		}
	}
}
?>

<?php if($db->getSetting('absence_confirmation_required') != 1 && $db->getSetting('absence_confirmation_required') != 2) { ?>
<div class="infobox yellow"><?php echo LANG['absence_confirmation_disabled']; ?></div>
<?php } elseif($currentUser->superadmin == 0 && count($adminRosters) == 0) { ?>
<div class="infobox yellow"><?php echo LANG['absence_confirmation_rights_missing']; ?></div>
<?php } else { ?>
<div class="contentbox">
	<h2><?php echo LANG['approve_absence']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
		<table class="data">
			<tr>
				<th><?php echo LANG['employee']; ?></th><th><?php echo LANG['begin']; ?></th><th><?php echo LANG['end']; ?></th><th><?php echo LANG['comment']; ?></th><th><?php echo LANG['confirmed_approved']; ?></th><th><?php echo LANG['action']; ?></th>
			</tr>
			<?php
			if($currentUser->superadmin > 0) {
				// superadmin can approve all absences
				echoUnapprovedAbsences(null);
			} else {
				// non-superadmins can approve absences of users in rosters for which he is admin for
				foreach($adminRosters as $ar) {
					echoUnapprovedAbsences($ar->roster_id);
				}
			}
			?>
		</table>
</div>
<?php } ?>
