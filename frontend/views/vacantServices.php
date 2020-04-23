<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

$plan = new plan($db);

if(isset($_POST['action'])) {
	if($_POST['action'] == 'swap_service'
	&& !empty($_POST['id'])) {
		$error = false;

		$swapService = $db->getSwapService($_POST['id']);
		if($swapService == null) $error = true;

		if(!$error) {
			$isUserAssignedRoster = false;
			foreach($db->getUserRosters($_SESSION['mp_userid']) as $ur) {
				if($ur->roster_id == $swapService->service_roster_id) {
					$isUserAssignedRoster = true;
					break;
				}
			}
			if(!$isUserAssignedRoster) $error = true;
		}

		// send ics cancel mail
		$plan = new plan($db);
		if(!$error) {
			$ps1 = $db->getPlannedService($swapService->planned_service_id);
			if($ps1->icsmail_sent != null && $ps1->icsmail_sent != 0
			&& tools::isValidEmail($db->getUser($ps1->user_id)->email)) {
				$error = !$plan->sendIcsMail($ps1, true);
			}
		}

		// update planned service
		if(!$error) $error = !$db->updatePlannedService(
			$swapService->planned_service_id, $swapService->planned_service_day, $swapService->service_id, $_SESSION['mp_userid']
		);
		// nicht benötigt, da updatePlannedService() mit REPLACE INTO alle Fremdschlüsselbeziehungen in SwapService auflöst
		//if(!$error) $error = !$db->removeSwapService($_POST['id']);

		// send ics mail
		if(!$error) {
			$ps2 = $db->getPlannedService($swapService->planned_service_id);
			if($ps1->icsmail_sent != null && $ps1->icsmail_sent != 0
			&& tools::isValidEmail($db->getUser($ps2->user_id)->email)) {
				$error = !$plan->sendIcsMail($ps2, false);
			}
		}

		if(!$error) {
			$info = 'Dienst wurde getauscht. Vielen Dank!';
			$infoclass = 'green';
		} else {
			$info = 'Dienst konnte nicht getauscht werden: '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
	elseif($_POST['action'] == 'take_vacant_service') {
		if($db->updatePlannedService(null, $_POST['day'], $_POST['service'], $currentUser->id)) {
			$info = 'Dienst wurde übernommen. Vielen Dank!';
			$infoclass = 'green';
		} else {
			$info = 'Dienst konnte nicht übernommen werden: '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
}
?>

<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<?php if(boolval($db->getSetting('sc_swap'))) { ?>
<div class="contentbox">
	<h2>Tauschgesuche</h2>
	<?php
		$allSwapServices = $db->getSwapServices();
		$swapServices = [];
		foreach($allSwapServices as $ss) {
			$isUserAssignedRoster = false;
			foreach($db->getUserRosters($currentUser->id) as $ur) {
				if($ur->roster_id == $ss->service_roster_id) {
					$swapServices[] = $ss;
					break;
				}
			}
		}
		if(count($swapServices) == 0) {
	?>
		<div class="infobox">Im Moment sind keine Dienste innerhalb der Ihnen zugeteilten Dienstpläne zum Tausch freigegeben</div>
	<?php } else { ?>
		<table class="data plan nocolumnlines">
			<tr>
				<th>Mitarbeiter</th>
				<th>Tag</th>
				<th>Dienst</th>
				<th>Kommentar</th>
				<th>Aktion</th>
			</tr>
			<?php foreach($swapServices as $ss) { ?>
				<tr>
					<td>
						<?php boxes::echoUser($db->getUser($ss->user_id)); ?>
					</td>
					<td>
						<?php
						$time = strtotime($ss->planned_service_day);
						echo strftime('%a', $time).', '.strftime(DATE_FORMAT, $time);
						?>
					</td>
					<td>
						<?php boxes::echoService($db->getService($ss->service_id), $ss->planned_service_day, $db); ?>
					</td>
					<td title="<?php echo htmlspecialchars($ss->comment); ?>">
						<?php echo htmlspecialchars(tools::shortText($ss->comment)); ?>
					</td>
					<td>
						<form method="POST" onsubmit="return confirm('Möchten Sie diesen Dienst übernehmen?')">
							<input type="hidden" name="action" value="swap_service">
							<input type="hidden" name="id" value="<?php echo $ss->id; ?>">
							<button><img src="img/ok.svg">&nbsp;Dienst übernehmen</button>
						</form>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
	</table>
</div>
<?php } ?>

<?php
$vacantServices = [];
foreach($db->getUserRosters($_SESSION['mp_userid']) as $ur) {
	// for each roster release
	foreach($db->getReleasedPlansByRoster($ur->roster_id) as $rp) {
		foreach($plan->getConsolidatedServicesByRosterAndDay($ur->roster_id, $rp->day) as $s) {
			$assignments = $db->getPlannedServicesWithUserByRosterAndServiceAndDay($ur->roster_id, $s->id, $rp->day);
			for($n=count($assignments); $n<$s->employees; $n++) {
				$vacantServices[] = [
					'roster_title' => $ur->roster_title,
					'time' => strtotime($rp->day),
					'day' => $rp->day,
					'service_id' => $s->id
				];
			}
		}
	}
}
?>
<div class="contentbox">
	<h2>Vakante Dienste</h2>
	<?php if(count($vacantServices) == 0) { ?>
		<div class="infobox">Im Moment sind keine Dienste innerhalb der Ihnen zugeteilten Dienstpläne unbesetzt</div>
	<?php } else { ?>
	<table class="data plan nocolumnlines">
		<tr>
			<th>Tag</th>
			<th>Dienst</th>
			<th>Aktion</th>
		</tr>
		<?php
		$lastTitle = null;
		foreach($vacantServices as $vs) {
			if($lastTitle != $vs['roster_title']) {
				echo '<tr><th colspan="3"><div class="infobox gray">'.htmlspecialchars($ur->roster_title).'</div></th></tr>';
				$lastTitle = $vs['roster_title'];
			}
		?>
		<tr>
			<td><?php echo strftime('%a', $vs['time']).', '.strftime(DATE_FORMAT, $vs['time']); ?></td>
			<td><?php boxes::echoService($db->getService($vs['service_id']), $vs['day'], $db); ?></td>
			<td>
				<form method="POST" onsubmit="return confirm('Möchten Sie diesen Dienst übernehmen?')">
					<input type="hidden" name="action" value="take_vacant_service">
					<input type="hidden" name="service" value="<?php echo $vs['service_id']; ?>">
					<input type="hidden" name="day" value="<?php echo $vs['day']; ?>">
					<button><img src="img/ok.svg">&nbsp;Dienst übernehmen</button>
				</form>
			</td>
		</tr>
		<?php } ?>
	</table>
	<?php } ?>
</div>
