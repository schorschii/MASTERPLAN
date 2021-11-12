<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">'.LANG['page_superadmin_right_needed'].'</div>');
}

// update
if(isset($_POST['writefile']) && isset($_POST['text'])) {
	$escapedFilename = str_replace('/','',$_POST['writefile']);
	if(file_exists(TEMPLATE_FILES.'/'.$escapedFilename) && file_put_contents(TEMPLATE_FILES.'/'.$escapedFilename, $_POST['text'])) {
		$info = LANG['template_saved'];
		$infoclass = 'green';
	} else {
		$info = LANG['template_could_not_be_saved'];
		$infoclass = 'red';
	}
}
?>

<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class='contentbox small'>
	<h2><?php echo LANG['existing_templates']; ?></h2>
	<img class="contentbox-embleme" src="img/template.svg">
	<form method="POST">
		<div>
			<select name="loadfile" class="fullwidth" autofocus="true">
				<?php
				$dir = new DirectoryIterator(TEMPLATE_FILES);
				foreach($dir as $fileinfo) {
					if(!$fileinfo->isDot()) {
						echo "<option>".htmlspecialchars($fileinfo->getFilename())."</option>";
					}
				}
				?>
			</select>
		</div>
		<button class="fullwidth margintop"><img src="img/refresh.svg">&nbsp;<?php echo LANG['load']; ?></button>
	</form>
</div>

<?php
	if(isset($_POST['loadfile'])) {
		$escapedFilename = str_replace('/','',$_POST['loadfile']);
		if(file_exists(TEMPLATE_FILES.'/'.$escapedFilename)) {
?>
	<div class='contentbox small'>
		<h2><?php echo LANG['edit_template']; ?></h2>
		<img class="contentbox-embleme" src="img/email.svg">
		<form method="POST">
			<div>
				<input type="text" readonly="true" name="writefile" class="fullwidth" value="<?php echo htmlspecialchars($escapedFilename); ?>">
				<textarea name="text" class="fullwidth" rows="10"><?php echo htmlspecialchars(file_get_contents(TEMPLATE_FILES.'/'.$escapedFilename)); ?></textarea>
			</div>
			<p>
				<h3><?php echo LANG['placeholder']; ?></h3>
				<div class="hint">$$BASE_URL$$ - <?php echo LANG['url_to_web_ui']; ?></div>
			</p>
			<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;<?php echo LANG['save']; ?></button>
		</form>
	</div>
<?php
		} else {
?>
	<div class='contentbox small'>
		<div class='infobox red'><?php echo LANG['file_could_not_be_loaded']; ?></div>
	</div>
<?php
		}
	}
?>
