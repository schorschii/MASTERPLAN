<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

$birthdayUsers = [];
foreach($db->getUsers() as $u) {
	if(trim($u->birthday) != '')
		$birthdayUsers[] = $u;
}
usort($birthdayUsers, 'sortBirthdays');

function sortBirthdays($a, $b) {
	$currentYearA = date('Y');
	$timeBirthdayA = strtotime($a->birthday);
	$timeNextBirthdayA = strtotime($currentYearA.'-'.date('m',$timeBirthdayA).'-'.date('d',$timeBirthdayA));
	if($timeNextBirthdayA < strtotime(date('Y-m-d')))
		$timeNextBirthdayA = strtotime('+ 1 year', $timeNextBirthdayA);

	$currentYearB = date('Y');
	$timeBirthdayB = strtotime($b->birthday);
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
	<h2>Kommende Geburtstage</h2>

	<table class="data">
		<tr>
			<th>Mitarbeiter</th><th>E-Mail</th><th>Geburtstag</th><th>Alter</th>
		</tr>
		<?php
		foreach($birthdayUsers as $u) {
			$timeBirthday = strtotime($u->birthday);
			$isToday = (date('m-d',$timeBirthday) == date('m-d'));

			$timeNextBirthday = strtotime(date('Y').'-'.date('m',$timeBirthday).'-'.date('d',$timeBirthday));
			if($timeNextBirthday < strtotime(date('Y-m-d')))
				$timeNextBirthday = strtotime('+ 1 year', $timeNextBirthday);

			echo '<tr class="'.($isToday ? 'mark' : '').'">';
			echo '<td>'; boxes::echoUser($u); echo '</td>';
			echo '<td><a href="mailto:'.htmlspecialchars($u->email).'">'.htmlspecialchars($u->email).'</a></td>';
			echo '<td>'.htmlspecialchars(strftime(DATE_FORMAT, $timeBirthday)).'</td>';
			echo '<td>'.(date('Y', $timeNextBirthday)-date('Y', $timeBirthday)).'</td>';
			echo '</tr>';
		}
		?>
	</table>
</div>
