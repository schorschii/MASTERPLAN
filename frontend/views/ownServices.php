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
	&& !empty($_POST['id']
	&& $showSwap)) {
		// check if planned service is still owned by this user
		$plannedService = $db->getPlannedService($_POST['id']);
		if($plannedService != null && $plannedService->user_id == $currentUser->id) {
			if($db->updateSwapService(null, $_POST['id'], $_POST['comment'])) {
				$info = LANG['service_released_for_swap'];
				$infoclass = 'green';

				// send mail to roster users
				if(boolval($db->getSetting('swap_mails'))) {
					$mailer = new mailer($db);
					$mailer->mailNewSwap($plannedService->service_roster_id);
				}
			} else {
				$info = LANG['error'].': '.$db->getLastStatement()->error;
				$infoclass = 'red';
			}
		} else {
			$info = LANG['service_could_not_be_released_for_swap_service_not_assigned_to_you'];
			$infoclass = 'yellow';
		}
	}
}
?>

<div class="contentbox">
	<h2><?php echo LANG['my_future_services']; ?></h2>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<?php if(count($plannedServices) == 0) { ?>
		<div class="infobox"><?php echo LANG['no_services_assigned_to_you']; ?></div>
	<?php } else { ?>
	<table class="data plan nocolumnlines">
		<tr>
			<th><?php echo LANG['day']; ?></th>
			<th><?php echo LANG['roster']; ?></th>
			<th><?php echo LANG['service']; ?></th>
			<?php if($showSwap) { ?>
			<th><?php echo LANG['action']; ?></th>
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
							<button><img src="img/swap.svg">&nbsp;<?php echo LANG['release_for_swap']; ?></button>
						</form>
					<?php } else { ?>
						<button disabled="true"><img src="img/swap.svg">&nbsp;<?php echo LANG['release_for_swap']; ?></button>
					<?php } ?>
					</td>
				<?php } ?>
		<?php } ?>
	</table>
	<?php } ?>
</div>

<script>
function swap() {
	var comment = prompt('<?php echo LANG['add_note_to_swap']; ?>');
	if(comment == null) return false;
	else iptComment.value = comment;
	return true;
}
</script>
