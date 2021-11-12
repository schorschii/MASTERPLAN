<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeHoliday' && !empty($_POST['id'])) {
		if($db->removeHoliday($_POST['id'])) {
			$info = LANG['holiday_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['holiday_could_not_be_removed'];
			$infoclass = 'red';
		}
	}
}
?>

<div class='contentbox'>
<h2><?php echo LANG['existing_holidays']; ?> (<?php echo count($db->getHolidays()); ?>)</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='editHoliday'>
		<button><img src='img/add.svg'>&nbsp;<?php echo LANG['holiday']; ?></button>
	</form>
</div>

<table class="data rowhover">
	<tr>
		<th><?php echo LANG['title']; ?></th><th><?php echo LANG['day']; ?></th><th><?php echo LANG['affected_services']; ?></th><th><?php echo LANG['action']; ?></th>
	</tr>
	<?php
	foreach($db->getHolidays() as $h) {
		$service_title = '<b>'.LANG['all'].'</b>';
		if($h->service_id != null) {
			$service_title = htmlspecialchars($h->service_shortname." ".$h->service_title);
		}

		echo '<tr>';
		echo '<td>'.htmlspecialchars($h->title).'</td>';
		echo '<td>'.htmlspecialchars($h->day).'</td>';
		echo '<td>'.$service_title.'</td>';
		echo '<td class="wrapcontent">'
			.'<a class="button" href="?view=editHoliday&id='.$h->id.'" title="'.LANG['edit'].'"><img src="img/edit.svg"></a>'
			.'<form method="POST" onsubmit="return confirm('."'".LANG['really_remove_this_holiday']."'".')">'
			.'<input type="hidden" name="action" value="removeHoliday">'
			.'<input type="hidden" name="id" value="'.$h->id.'">'
			.'<button title="'.LANG['remove'].'"><img src="img/delete.svg"></button>'
			.'</form>'
			.'</td>';
		echo '</tr>';
	}
	?>
</table>
</div>
