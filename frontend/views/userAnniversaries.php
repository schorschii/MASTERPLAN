<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

$users = [];
foreach($db->getUsers() as $u) {
	if(trim($u->start_date) != '')
		$users[] = $u;
}
usort($users, 'sortAnniversaries');

function sortAnniversaries($a, $b) {
	$currentYearA = date('Y');
	$timeBirthdayA = strtotime($a->start_date);
	$timeNextBirthdayA = strtotime($currentYearA.'-'.date('m',$timeBirthdayA).'-'.date('d',$timeBirthdayA));
	if($timeNextBirthdayA < strtotime(date('Y-m-d')))
		$timeNextBirthdayA = strtotime('+ 1 year', $timeNextBirthdayA);

	$currentYearB = date('Y');
	$timeBirthdayB = strtotime($b->start_date);
	$timeNextBirthdayB = strtotime($currentYearB.'-'.date('m',$timeBirthdayB).'-'.date('d',$timeBirthdayB));
	if($timeNextBirthdayB < strtotime(date('Y-m-d')))
		$timeNextBirthdayB = strtotime('+ 1 year', $timeNextBirthdayB);

	if($timeNextBirthdayA == $timeNextBirthdayB) {
		return 0;
	}
	return ($timeNextBirthdayA < $timeNextBirthdayB) ? -1 : 1;
}
?>

<div class='contentbox small'>
	<h2><?php echo LANG['upcoming_anniversaries']; ?></h2>

	<table class="data">
		<tr>
			<th><?php echo LANG['employee']; ?></th><th><?php echo LANG['email']; ?></th><th><?php echo LANG['work_begin']; ?></th><th><?php echo LANG['employee_since']; ?></th>
		</tr>
		<?php
		foreach($users as $u) {
			$timeStart = strtotime($u->start_date);
			$isToday = (date('m-d',$timeStart) == date('m-d'));

			$timeNext = strtotime(date('Y').'-'.date('m',$timeStart).'-'.date('d',$timeStart));
			if($timeNext < strtotime(date('Y-m-d')))
				$timeNext = strtotime('+ 1 year', $timeNext);

			echo '<tr class="'.($isToday ? 'mark' : '').'">';
			echo '<td>'; boxes::echoUser($u); echo '</td>';
			echo '<td><a href="mailto:'.htmlspecialchars($u->email).'">'.htmlspecialchars($u->email).'</a></td>';
			echo '<td>'.htmlspecialchars(strftime(DATE_FORMAT, $timeStart)).'</td>';
			echo '<td>'.htmlspecialchars(strftime(DATE_FORMAT, $timeNext)).': '.(date('Y', $timeNext)-date('Y', $timeStart)).' Jahr(en)</td>';
			echo '</tr>';
		}
		?>
	</table>
</div>
