<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

if(!boolval($db->getSetting('sc_absence'))) {
	die('<div class="infobox yellow">Dieses Modul wurde durch Ihren Administrator deaktiviert</div>');
}

if(isset($_POST['action'])) {
	if($_POST['action'] == 'absence' && !empty($_POST['start']) && !empty($_POST['end'])) {

		$userid = $_SESSION['mp_userid'];
		if($currentUser->superadmin > 0 && !empty($_POST['user']))
			$userid = $_POST['user'];

		$approved = 1;
		if(boolval($db->getSetting('absence_confirmation_required')))
			$approved = 0;

		if(strtotime($_POST['start']) <= strtotime($_POST['end'])) {

		if(abs(strtotime($_POST['start']) - strtotime($_POST['end'])) < 60*60*24*60) {

		if($db->updateAbsence(null, $userid, $_POST['type'], $_POST['start'], $_POST['end'], $_POST['comment'], $approved)) {
			// check if user is assigned to a service in this time range
			$hasService = false;
			for($i = strtotime($_POST['start']); $i <= strtotime($_POST['end']); $i = $i + 86400) {
				$services = $db->getPlannedServicesByUserAndDay($userid, date('Y-m-d', $i));
				if(count($services) > 0) {
					$hasService = true;
					break;
				}
			}

			if($hasService) {
				$info = LANG['absence_saved_conflict_with_services'];
				$infoclass = 'yellow';
			} else {
				$info = LANG['absence_saved'];
				$infoclass = 'green';
			}

			// send mail to roster admin
			if($approved == 0 && boolval($db->getSetting('absence_mails'))) {
				$mailer = new mailer($db);
				$mailer->mailNewAbsence($userid);
			}

		} else {
			$info = LANG['absence_could_not_be_saved'].' '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}

		} else {
			$info = LANG['absence_too_long'];
			$infoclass = 'red';
		}

		} else {
			$info = LANG['end_date_before_start_date'];
			$infoclass = 'red';
		}

	}
	elseif($_POST['action'] == 'removeAbsence') {
		if($db->removeAbsence($_POST['id'])) {
			$info = LANG['absence_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['absence_could_not_be_removed'].' '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
}
?>

<div class="contentbox small">
	<h2><?php echo LANG['register_absence']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST">
		<input type="hidden" id="action" name="action" value="absence">
		<table>
			<?php if($currentUser->superadmin > 0) { ?>
				<tr>
					<th><?php echo LANG['employee']; ?>:</th>
					<td>
						<select name="user">
							<?php
							$preselectUser = $currentUser->id;
							if(isset($_POST['user'])) $preselectUser = intval($_POST['user']);
							foreach($db->getUsers() as $u) {
							?>
								<option <?php echo htmlinput::selectIf($u->id,$preselectUser); ?>><?php echo trim(htmlspecialchars($u->fullname)." (".htmlspecialchars($u->login).")"); ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr>
					<th></th>
					<td><button onclick="action.value='view_foreign_absences'"><img src="img/absent.svg">&nbsp;<?php echo LANG['show_absences_of_this_employee']; ?></button></td>
				</tr>
			<?php } ?>
			<tr>
				<th><?php echo LANG['type']; ?>:</th>
				<td>
					<select name="type" autofocus="true">
						<?php foreach($db->getAbsentTypes() as $at) {
							echo "<option value='".$at->id."'>".htmlspecialchars($at->shortname)." ".htmlspecialchars($at->title)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['begin']; ?>:</th>
				<td><input type="date" name="start" value=""></td>
			</tr>
			<tr>
				<th><?php echo LANG['end']; ?>:</th>
				<td><input type="date" name="end" value=""></td>
			</tr>
			<tr>
				<th><?php echo LANG['comment']; ?>:</th>
				<td><input type="text" name="comment" value="" placeholder="(<?php echo LANG['optional']; ?>)"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
		</table>
	</form>
</div>

<div class="contentbox small">
	<?php
	$userid = $currentUser->id;
	if($currentUser->superadmin > 0 && isset($_POST['user'])) {
		$user = $db->getUser($_POST['user']);
		if($user != null) {
			$userid = $user->id;
			echo '<h2>'.str_replace('%1', htmlspecialchars($user->fullname).' ('.htmlspecialchars($user->login).')', LANG['absences_of']).'</h2>';
		}
	} else {
		echo '<h2>'.LANG['my_saved_absences'].'</h2>';
	}
	$absences = $db->getFutureAbsencesByUser($userid);
	if(count($absences) == 0) {
	?>
		<div class="infobox"><?php echo LANG['no_absences_in_the_future']; ?></div>
	<?php } else { ?>
		<div class="marginbottom">
			<form method="GET" class="inlineblock" action="export.php" target="_blank">
				<input type="hidden" name="export" value="absence">
				<input type="hidden" name="type" value="pdf">
				<input type="hidden" name="user" value="<?php echo $currentUser->id; ?>">
				<button><img id="btnExportPDF" src="img/export.svg">&nbsp;<?php echo LANG['pdf_export']; ?></button>
			</form>
		</div>
		<table class="data">
			<tr>
				<th><?php echo LANG['short_name']; ?></th><th><?php echo LANG['begin']; ?></th><th><?php echo LANG['end']; ?></th><th><?php echo LANG['confirmed_approved']; ?></th><th><?php echo LANG['action']; ?></th>
			</tr>
			<?php
			$aplan = new autoplan($db);
			foreach($absences as $a) {
				$approved = $aplan->isAbsenceApproved($a);

				$approved_user_text = '';
				if($a->approved1_by_user_id != null)
					$approved_user_text .= ' ('.$db->getUser($a->approved1_by_user_id)->fullname.')';
				if($a->approved2_by_user_id != null)
					$approved_user_text .= ' ('.$db->getUser($a->approved2_by_user_id)->fullname.')';

				echo '<tr>'
					.'<td>'
					.htmlspecialchars($a->absent_type_shortname)
					.($a->comment!='' ? '<img src="img/info-gray.svg" title="'.htmlspecialchars($a->comment).'">' : '')
					.'</td>'
					.'<td>'.htmlspecialchars(strftime(DATE_FORMAT, strtotime($a->start))).'</td>'
					.'<td>'.htmlspecialchars(strftime(DATE_FORMAT, strtotime($a->end))).'</td>'
					.'<td>'.($approved ? LANG['yes'] : LANG['no']).htmlspecialchars($approved_user_text).'</td>'
					.'<td class="wrapcontent center">'
					. '<form method="POST" onsubmit="return confirm('."'".LANG['really_remove_absence']."'".')">'
					. '<input type="hidden" name="action" value="removeAbsence">'
					. '<input type="hidden" name="id" value="'.$a->id.'">'
					. '<button><img src="img/delete.svg"></button>'
					. '</form>'
					.'</td>'
					.'</tr>';
			} ?>
		</table>
	<?php } ?>
</div>
