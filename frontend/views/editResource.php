<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

// create/update
if(!empty($_POST['title'])) {
	$success = false;
	if(isset($_POST['id'])) {
		$success = $db->updateResource(
			$_POST['id'], $_POST['type'], $_POST['title'], $_POST['description'], $_POST['icon'], $_POST['color']
		);
	} else {
		$success = $db->createResource(
			$_POST['type'], $_POST['title'], $_POST['description'], $_POST['icon'], $_POST['color']
		);
	}
	if($success) {
		if(isset($_POST['id'])) {
			header('Location: index.php?view=resources');
			die();
		} else {
			$info = LANG['resource_saved'];
			$infoclass = 'green';
		}
	} else {
		$info = LANG['error'].': '.$db->getLastStatement()->error;
		$infoclass = 'red';
	}
}


// display
$prefillType = '';
$prefillTitle = '';
$prefillDescription = '';
$prefillColor = '#F5F5F5';
$prefillIcon= '';

$r = null;
if(isset($_GET['id'])) {
	$r = $db->getResource($_GET['id']);
	if($r == null) die('<div class="infobox red">'.LANG['not_found'].'</div>');
	$prefillType = $r->type;
	$prefillTitle = $r->title;
	$prefillDescription = $r->description;
	$prefillColor = $r->color;
	$prefillIcon= $r->icon;
}
?>

<div class='contentbox small'>
	<?php if($r == null) { ?>
		<h2><?php echo LANG['new_resource']; ?></h2>
	<?php } else { ?>
		<h2><?php echo LANG['edit_resource']; ?></h2>
	<?php } ?>
	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>

	<form method="POST" id="frmService">
		<?php if($r != null) { ?>
			<input type="hidden" name="id" value="<?php echo $r->id; ?>">
		<?php } ?>

		<table>
			<tr>
				<th><?php echo LANG['type']; ?>:</th>
				<td><input type="text" name="type" maxlength="10" autofocus="true" placeholder="Auto, Telefon, Raum, Notebook, ..." value="<?php echo htmlspecialchars($prefillType); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['title']; ?>:</th>
				<td><input type="text" name="title" value="<?php echo htmlspecialchars($prefillTitle); ?>"></td>
			</tr>
			<tr>
				<th><?php echo LANG['description']; ?>:</th>
				<td><textarea name="description" placeholder="(<?php echo LANG['optional']; ?>)"><?php echo htmlspecialchars($prefillDescription); ?></textarea></td>
			</tr>
			<tr>
				<th><?php echo LANG['icon']; ?>:</th>
				<td>
					<?php
					$path = 'img/resource_types';
					$dir = new DirectoryIterator($path);
					foreach($dir as $file) {
						if($file->isDot()) continue;
						echo "<label title='".htmlspecialchars($file->getFilename())."'><input type='radio' class='autosize' name='icon' value='".htmlspecialchars($path.'/'.$file->getFilename())."' ".($path.'/'.$file->getFilename()==$prefillIcon ? "checked='true'" : "").">&nbsp;<img src='img/resource_types/".htmlspecialchars($file->getFilename())."'></label>&nbsp;&nbsp;&nbsp;";
					} ?>
				</td>
			</tr>
			<tr>
				<th><?php echo LANG['color']; ?>:</th>
				<td><input type="color" name="color" value="<?php echo htmlspecialchars($prefillColor); ?>"></td>
			</tr>
			<tr>
				<th></th>
				<td><button><img src='img/ok.svg'>&nbsp;<?php echo LANG['save']; ?></button></td>
			</tr>
		</table>
	</form>
</div>
