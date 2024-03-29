<?php
$info = null;
$infotype = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

$absentType = null;
$prefillShortname = '';
$prefillTitle = '';
$prefillColor = '';

if(isset($_POST['action'])) {
	if($_POST['action'] == 'edit') {
		$absentType = $db->getAbsentType($_POST['id']);
		if($absentType == null) {
			die('<div class="infobox red">'.LANG['not_found'].'</div>');
		} else {
			$prefillShortname = $absentType->shortname;
			$prefillTitle = $absentType->title;
			$prefillColor = $absentType->color;
		}
	}
	elseif($_POST['action'] == 'do_create') {
		if($db->createAbsentType($_POST['shortname'], $_POST['title'], $_POST['color'])) {
			$info = LANG['absence_type_created'];
			$infoclass = 'green';
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
	if($_POST['action'] == 'do_edit') {
		if($db->updateAbsentType($_POST['id'], $_POST['shortname'], $_POST['title'], $_POST['color'])) {
			$info = LANG['absence_type_edited'];
			$infoclass = 'green';
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
	elseif($_POST['action'] == 'do_remove') {
		if($db->removeAbsentType($_POST['id'])) {
			$info = LANG['absence_type_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
}
?>

<div class="contentbox small">
<?php if($absentType == null) { ?>
	<h2><?php echo LANG['create_absence_type']; ?></h2>
<?php } else { ?>
	<h2><?php echo LANG['edit_absence_type']; ?></h2>
<?php } ?>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>
	<form method="POST">
		<?php if($absentType != null) { ?>
			<input type="hidden" name="action" value="do_edit">
			<input type="hidden" name="id" value="<?php echo $absentType->id; ?>">
		<?php } else { ?>
			<input type="hidden" name="action" value="do_create">
		<?php } ?>
		<table>
			<tr>
				<th><?php echo LANG['short_name']; ?>:</th>
				<td><input type="text" name="shortname" value="<?php echo htmlspecialchars($prefillShortname); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['description']; ?>:</th>
				<td><input type="text" name="title" value="<?php echo htmlspecialchars($prefillTitle); ?>" placeholder="(optional)"></td>
			</tr>
			<tr>
				<th><?php echo LANG['color']; ?>:</th>
				<td><input type="color" name="color" value="<?php echo htmlspecialchars($prefillColor); ?>"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
			<?php if($absentType != null) { ?>
			<tr>
				<th></th>
				<td><a class='button fullwidth' href='?view=editAbsentTypes'><img src='img/add.svg'>&nbsp;<?php echo LANG['new_absence_type']; ?></a></td>
			</tr>
			<?php } ?>
		</table>
	</form>
</div>

<div class="contentbox small">
	<h2><?php echo LANG['existing_absence_types']; ?></h2>
	<?php
	$absentTypes = $db->getAbsentTypes();
	if(count($absentTypes) == 0) {
	?>
		<div class="infobox"><?php echo LANG['no_absence_types_defined']; ?></div>
	<?php } else { ?>
		<table class="data">
			<tr>
				<th><?php echo LANG['short_name']; ?></th><th><?php echo LANG['description']; ?></th><th><?php echo LANG['color']; ?></th><th><?php echo LANG['action']; ?></th>
			</tr>
			<?php foreach($absentTypes as $at) { ?>
				<tr>
					<td><?php echo htmlspecialchars($at->shortname); ?></td>
					<td><?php echo htmlspecialchars($at->title); ?></td>
					<td><span class='colorpreview' style='background-color:<?php echo htmlspecialchars($at->color); ?>'></span></td>
					<td class="wrapcontent">
						<form method="POST">
							<input type="hidden" name="action" value="edit">
							<input type="hidden" name="id" value="<?php echo $at->id; ?>">
							<button class="autowidth" title="<?php echo LANG['edit']; ?>"><img src="img/edit.svg"></button>
						</form>
						<form method="POST" onsubmit="return confirm('<?php echo LANG['really_remove_absence_type']; ?>')">
							<input type="hidden" name="action" value="do_remove">
							<input type="hidden" name="id" value="<?php echo $at->id; ?>">
							<button class="autowidth" title="<?php echo LANG['remove']; ?>"><img src="img/delete.svg"></button>
						</form>
					</td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
</div>
