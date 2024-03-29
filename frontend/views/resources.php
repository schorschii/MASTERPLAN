<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'removeResource' && !empty($_POST['id'])) {
		if($db->removeResource($_POST['id'])) {
			$info = LANG['resource_removed'];
			$infoclass = 'green';
		} else {
			$info = LANG['error'].': '.$db->getLastStatement()->error;
			$infoclass = 'red';
		}
	}
}
?>

<div class='contentbox'>
<h2><?php echo LANG['existing_resources']; ?> (<?php echo count($db->getResources()); ?>)</h2>
<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class="toolbar marginbottom">
	<form method='GET' class='inlineblock'>
		<input type='hidden' name='view' value='editResource'>
		<button><img src='img/add.svg'>&nbsp;<?php echo LANG['resource']; ?></button>
	</form>
</div>

<table class="data rowhover">
	<tr>
		<th><?php echo LANG['icon']; ?>/<?php echo LANG['color']; ?></th><th><?php echo LANG['type']; ?></th><th><?php echo LANG['title']; ?></th><th><?php echo LANG['description']; ?></th><th><?php echo LANG['action']; ?></th>
	</tr>
	<?php
	foreach($db->getResources() as $r) {
		echo '<tr>';
		echo '<td>'
			.(($r->icon != null && $r->icon != '') ? ' <img src="'.htmlspecialchars($r->icon).'">' : '')
			.' <span class="colorpreview" style="background-color:'.htmlspecialchars($r->color).'"></span>'
			.'</td>';
		echo '<td>'.htmlspecialchars($r->type).'</td>';
		echo '<td>'.htmlspecialchars($r->title).'</td>';
		echo '<td>'.htmlspecialchars(tools::shortText($r->description)).'</td>';
		echo '<td class="wrapcontent">'
			.'<a class="button" href="?view=editResource&id='.$r->id.'" title="'.LANG['edit'].'"><img src="img/edit.svg"></a>'
			.'<form method="POST" onsubmit="return confirm('."'".LANG['really_remove_this_resource']."'".')">'
			.'<input type="hidden" name="action" value="removeResource">'
			.'<input type="hidden" name="id" value="'.$r->id.'">'
			.'<button title="'.LANG['remove'].'"><img src="img/delete.svg"></button>'
			.'</form>'
			.'</td>';
		echo '</tr>';
	}
	?>
</table>
</div>
