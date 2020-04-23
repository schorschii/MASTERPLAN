<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

if(isset($_POST['action'])) {
	if($_POST['action'] == 'absence' && !empty($_POST['start']) && !empty($_POST['end'])) {

		$userid = $_POST['user'];
		if(isAllowedForUser($userid)) {

			if(strtotime($_POST['start']) <= strtotime($_POST['end'])) {

				if(abs(strtotime($_POST['start']) - strtotime($_POST['end'])) < 60*60*24*60) {

					$result = $db->updateAbsence(null, $userid, $_POST['type'], $_POST['start'], $_POST['end'], $_POST['comment'], 1);
					if($result) {
						$db->approveAbsence1($result, 1 , $currentUser->id);
						$db->approveAbsence2($result, 1 , $currentUser->id);

						// check if user is assigned to a service in this time range
						$plan = new plan($db);
						$error = false;
						$hasServices = 0;
						for($i = strtotime($_POST['start']); $i <= strtotime($_POST['end']); $i = $i + 86400) {
							$services = $db->getPlannedServicesByUserAndDay($userid, date('Y-m-d', $i));
							foreach($services as $ps) {
								if(!$plan->removeAssignment($ps->id)) {
									$error = true;
									break;
								}
							}
							$hasServices += count($services);
						}

						if($error) {
							$info = 'Abwesenheit wurde eingetragen. Dienste konnten nicht vollständig als vakant markiert werden.';
							$infoclass = 'red';
						} else {
							$info = 'Abwesenheit wurde eingetragen. '.$hasServices.' Dienst(e) wurden dadurch vakant.';
							$infoclass = 'green';
						}

					} else {
						$info = 'Abwesenheit konnte nicht eingetragen werden: '.$db->getLastStatement()->error;
						$infoclass = 'red';
					}

				} else {
					$info = 'Der angegebene Zeitraum ist zu groß (max. 60 Tage). Bitte teilen Sie die Abwesenheiten ggf. in mehrere Teile auf.';
					$infoclass = 'red';
				}

			} else {
				$info = 'Enddatum liegt vor dem Startdatum!';
				$infoclass = 'red';
			}

		}

	}
	elseif($_POST['action'] == 'removeAbsence') {
		if($db->removeAbsence($_POST['id'])) {
			$info = 'Abwesenheit wurde entfernt';
			$infoclass = 'green';
		} else {
			$info = 'Abwesenheit konnte nicht entfernt werden: '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
}

function isAllowedForUser($user_id) {
	global $db;
	global $perm;
	global $currentUser;
	if($currentUser->superadmin > 0) return true;
	foreach($db->getUserRosters($user_id) as $ur) {
		if($perm->isUserAdminForRoster($currentUser, $ur->roster_id))
			return true;
	}
	return false;
}
?>

<div class="contentbox small">
	<h2>Kurzfristige Abwesenheit eintragen</h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<p>
		Mit dieser Funktion können Dienstplan-Admins Abwesenheiten für dem Dienstplan zugeordnete Mitarbeiter eintragen.
	</p>
	<p>
		Im Unterschied zum Modul "Abwesenheiten eintragen" werden hier alle dem Mitarbeiter zugewiesenen Dienste innerhalb des gewählten Zeitraums entfernt, d.h. sie werden sofort vakant.
	</p>
	<form method="POST" onsubmit="return confirm('Hiermit werden alle dem Mitarbeiter zugeordneten Dienste innerhalb des ausgewählten Zeitraums vakant')">
		<input type="hidden" id="action" name="action" value="absence">
		<table>
			<tr>
				<th>Mitarbeiter:</th>
				<td>
					<select name="user" autofocus="true">
						<?php
						foreach($db->getUsers() as $u) {
							if(!isAllowedForUser($u->id)) continue;
						?>
							<option <?php echo htmlinput::selectIf($u->id,null); ?>><?php echo trim(htmlspecialchars($u->fullname)." (".htmlspecialchars($u->login).")"); ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Art:</th>
				<td>
					<select name="type">
						<?php foreach($db->getAbsentTypes() as $at) {
							echo "<option value='".$at->id."'>".htmlspecialchars($at->shortname)." ".htmlspecialchars($at->title)."</option>";
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Beginn:</th>
				<td><input type="date" name="start" value=""></td>
			</tr>
			<tr>
				<th>Ende:</th>
				<td><input type="date" name="end" value=""></td>
			</tr>
			<tr>
				<th>Kommentar:</th>
				<td><input type="text" name="comment" value="" placeholder="(optional)"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src="img/ok.svg">&nbsp;Speichern</button></td>
			</tr>
			<tr>
				<th></th>
				<td><a class="button fullwidth" href="?view=absence"><img src="img/absent.svg">&nbsp;Zum normalen Abwesenheits-Modul springen</a></td>
			</tr>
		</table>
	</form>
</div>
