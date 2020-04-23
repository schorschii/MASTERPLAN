<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

// update
if(isset($_POST['writefile']) && isset($_POST['text'])) {
	$escapedFilename = str_replace('/','',$_POST['writefile']);
	if(file_exists(TEMPLATE_FILES.'/'.$escapedFilename) && file_put_contents(TEMPLATE_FILES.'/'.$escapedFilename, $_POST['text'])) {
		$info = "Vorlage gespeichert";
		$infoclass = "green";
	} else {
		$info = "Vorlage konnte nicht gespeichert werden";
		$infoclass = "red";
	}
}
?>

<?php if($info != null) { ?>
	<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
<?php } ?>

<div class='contentbox small'>
	<h2>Vorhandene Vorlagen</h2>
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
		<button class="fullwidth margintop"><img src="img/refresh.svg">&nbsp;Laden</button>
	</form>
</div>

<?php
	if(isset($_POST['loadfile'])) {
		$escapedFilename = str_replace('/','',$_POST['loadfile']);
		if(file_exists(TEMPLATE_FILES.'/'.$escapedFilename)) {
?>
	<div class='contentbox small'>
		<h2>Vorlage bearbeiten</h2>
		<img class="contentbox-embleme" src="img/email.svg">
		<form method="POST">
			<div>
				<input type="text" readonly="true" name="writefile" class="fullwidth" value="<?php echo htmlspecialchars($escapedFilename); ?>">
				<textarea name="text" class="fullwidth" rows="10"><?php echo htmlspecialchars(file_get_contents(TEMPLATE_FILES.'/'.$escapedFilename)); ?></textarea>
			</div>
			<p>
				<h3>Platzhalter</h3>
				<div class="hint">$$BASE_URL$$ - Die URL zur Web-GUI</div>
			</p>
			<button class="fullwidth margintop"><img src="img/ok.svg">&nbsp;Speichern</button>
		</form>
	</div>
<?php
		} else {
?>
	<div class='contentbox small'>
		<div class='infobox red'>Datei kann nicht geladen werden</div>
	</div>
<?php
		}
	}
?>
