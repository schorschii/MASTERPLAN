<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

$preselectUser = null;
$prefillStartMonth = date('Y-m', strtotime('last month'));
$prefillEndMonth = date('Y-m', strtotime('next month'));

if(isset($_GET['user']))
	$preselectUser = $_GET['user'];
if(isset($_GET['start_month']))
	$prefillStartMonth = $_GET['start_month'];
if(isset($_GET['end_month']))
	$prefillEndMonth = $_GET['end_month'];
?>

<div class="contentbox">
	<h2><?php echo LANG['analysis_statistic']; ?></h2>
	<form method="GET">
		<input type="hidden" name="view" value="stat">
		<div class="inlineblock">
			<?php echo LANG['employee']; ?>:
			<select name="user">
				<?php
				foreach($db->getRosters() as $r) {
					if(!$perm->isUserAdminForRoster($currentUser, $r->id)) continue;
					echo "<optgroup label='".htmlspecialchars($r->title)."'>";
					foreach($db->getUsersByRoster($r->id) as $u) {
						echo "<option ".htmlinput::selectIf($u->id,$preselectUser).">".htmlspecialchars($u->fullname)."</option>";
					}
					echo "</optgroup>";
				}
				?>
			</select>
		</div>
		<div class="inlineblock">
			<?php echo LANG['start']; ?>:
			<input type="month" id="month1" name="start_month" value="<?php echo htmlspecialchars($prefillStartMonth); ?>">
		</div>
		<div class="inlineblock">
			<?php echo LANG['end']; ?>:
			<input type="month" id="month2" name="end_month" value="<?php echo htmlspecialchars($prefillEndMonth); ?>">
		</div>
		<button><img src="img/refresh.svg">&nbsp;<?php echo LANG['show']; ?></button>
	</form>
</div>

<?php
if(isset($_GET['user']) && isset($_GET['start_month']) && isset($_GET['end_month'])) {

$u = $db->getUser($_GET['user']);
if($u == null) die('<div class="infobox error">'.LANG['not_found'].'</div>');

$aplan = new autoplan($db);

$start = new DateTime($_GET['start_month']);
$start->modify('first day of this month');

$end = new DateTime($_GET['end_month']);
$end->modify('first day of next month');

$interval = DateInterval::createFromDateString('1 month');
$period   = new DatePeriod($start, $interval, $end);

$data = [];
$graphdata = [];
$sumHours = 0;
$sumServices = 0;
$sumMaxHoursPerMonth = 0;
foreach($period as $dt) {
	$month = $aplan->getWorkByUserAndTimespan(
		$u->id, strtotime(date('Y-m-01', $dt->getTimestamp())), strtotime(date('Y-m-t', $dt->getTimestamp()))
	);
	$sumHours += $month['hours'];
	$sumServices += $month['services'];
	if($u->max_hours_per_month > 0)
		$sumMaxHoursPerMonth += $u->max_hours_per_month;
	$data[] = [
		'span' => strftime('%B %Y', $dt->getTimestamp()),
		'hours' => $month['hours'],
		'services' => $month['services'],
		'max_hours_per_month' => $u->max_hours_per_month
	];
	if($u->max_hours_per_month < 0) {
		$graphdata[] = [
			$dt->format("Y-m"),
			$month['hours']
		];
	} else {
		$graphdata[] = [
			$dt->format("Y-m"),
			$month['hours'],
			$u->max_hours_per_month
		];
	}
}


$plot = new PHPlot_truecolor(1280, 720);

$plot->SetFont('x_label','4');
$plot->SetFont('y_label','4');
#$plot->SetDataColors(array('#05AA05'));
$plot->SetLineWidths(array(3));
$plot->SetBackgroundColor('white');
$plot->SetTransparentColor('white');
$plot->SetImageBorderType('none');

$plot->SetFontTTF('title', '../lib/phplot/fonts/Khula-Bold.ttf', 14);
$plot->SetFontTTF('legend', '../lib/phplot/fonts/Khula-Light.ttf', 13);
$plot->SetFontTTF('x_label', '../lib/phplot/fonts/Khula-Light.ttf', 12);
$plot->SetFontTTF('y_label', '../lib/phplot/fonts/Khula-Light.ttf', 12);

$plot->SetPlotType('lines');
$plot->SetDataValues($graphdata);

$plot->SetTitle(LANG['utilization'].' '.$u->fullname);
$plot->SetLegend(array(LANG['hours'], LANG['max_hrs']));

#$plot->SetPlotAreaWorld(NULL, 0, NULL, 100);
$plot->SetXTickLabelPos('none'); $plot->SetXTickPos('none');
$plot->SetPrintImage(false); $plot->DrawGraph();

#echo "<pre>"; var_dump($data); echo "</pre>"; // debug
?>

<div class="contentbox">
	<table class="data plan timetable">
		<tr>
			<th><?php echo LANG['time_span']; ?></th>
			<th><?php echo LANG['services']; ?></th>
			<th><?php echo LANG['hours']; ?></th>
			<th><?php echo LANG['max_hrs_month']; ?></th>
		</tr>
		<?php foreach($data as $d) { ?>
			<tr class="topborder">
				<td><?php echo htmlspecialchars($d['span']); ?></td>
				<td><?php echo htmlspecialchars($d['services']); ?></td>
				<td><?php echo htmlspecialchars($d['hours']); ?></td>
				<td><?php echo htmlspecialchars($d['max_hours_per_month']); ?></td>
			</tr>
		<?php } ?>
		<tr>
			<th><?php echo LANG['total']; ?></th>
			<td><?php echo $sumServices; ?></td>
			<td><?php echo $sumHours; ?></td>
			<td><?php echo $sumMaxHoursPerMonth; ?></td>
		</tr>
	</table>
</div>

<div class="contentbox graph">
	<img src="<?php echo $plot->EncodeImage();?>" alt="Graph">
</div>

<?php
}
?>
