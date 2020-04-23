<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();

$showSwap = boolval($db->getSetting('sc_swap'));

// query planned services
$plannedServices = [];
$allPlannedServices = $db->getFuturePlannedServicesByUser($currentUser->id);
// check if roster is released
foreach($allPlannedServices as $ps) {
	$released = false;
	foreach($db->getReleasedPlansByRoster($ps->service_roster_id) as $rp) {
		if(strtotime($rp->day) == strtotime($ps->day)) {
			$released = true;
			break;
		}
	}
	if($released) $plannedServices[] = $ps;
}

// release service for swap if requested
if(isset($_POST['action'])) {
	if($_POST['action'] == 'swapservice'
	&& !empty($_POST['id'])) {
		// check if planned service is still owned by this user
		$plannedService = $db->getPlannedService($_POST['id']);
		if($plannedService != null && $plannedService->user_id == $currentUser->id) {
			if($db->updateSwapService(null, $_POST['id'], $_POST['comment'])) {
				$info = 'Dienst wurde zum Tausch freigegeben. Sie sind weiterhin im Dienstplan eingetragen, bis ein Tauschpartner zugestimmt hat.';
				$infoclass = 'green';

				// send mail to roster users
				if(boolval($db->getSetting('swap_mails'))) {
					$mailer = new mailer($db);
					$mailer->mailNewSwap($plannedService->service_roster_id);
				}
			} else {
				$info = 'Tauschgesuch konnte nicht erstellt werden: '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = 'Tauschgesuch konnte nicht erstellt werden, da der Dienst Ihnen nicht mehr zugeordnet ist';
			$infoclass = 'yellow';
		}
	}
}
?>

<div class="contentbox">
	<h2>Meine kommenden Dienste</h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<?php if(count($plannedServices) == 0) { ?>
		<div class="infobox">Im Moment sind Ihnen keine Dienste zugeteilt</div>
	<?php } else { ?>
	<table class="data plan nocolumnlines">
		<tr>
			<th>Tag</th>
			<th>Dienstplan</th>
			<th>Dienst</th>
			<?php if($showSwap) { ?>
			<th>Aktion</th>
			<?php } ?>
		</tr>
		<?php
		foreach($plannedServices as $ps) { ?>
			<tr>
				<td>
					<?php
					$time = strtotime($ps->day);
					echo strftime('%a', $time).', '.strftime(DATE_FORMAT, $time);
					?>
				</td>
				<td>
					<?php
					echo htmlspecialchars($db->getRoster($ps->service_roster_id)->title);
					?>
				</td>
				<td>
					<?php echo boxes::echoService($db->getService($ps->service_id), $ps->day, $db); ?>
				</td>
				<?php if($showSwap) { ?>
					<td>
					<?php if($db->getSwapServiceByPlannedServiceId($ps->id) == null) { ?>
						<form method="POST" onsubmit="return swap()">
							<input type="hidden" name="action" value="swapservice">
							<input type="hidden" name="id" value="<?php echo $ps->id; ?>">
							<input type="hidden" name="comment" id="iptComment" value="">
							<button><img src="img/swap.svg">&nbsp;Zum Tausch freigeben</button>
						</form>
					<?php } else { ?>
						<button disabled="true"><img src="img/swap.svg">&nbsp;Zum Tausch freigegeben</button>
					<?php } ?>
					</td>
				<?php } ?>
		<?php } ?>
	</table>
	<?php } ?>
</div>

<script>
function swap() {
	var comment = prompt('Möchten Sie ein Kommentar zum Tauschgesuch hinzufügen?');
	if(comment == null) return false;
	else iptComment.value = comment;
	return true;
}
</script>
