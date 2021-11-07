<?php
// rights check
if(!isset($currentUser)) die();
$adminRosters = $db->getUserRostersAdmin($currentUser->id);
$userRosters = $db->getUserRosters($currentUser->id);
?>

<div class='contentbox small'>
	<img id='logo' src='img/logo.png'>
	<div class='start subtitle'>web based open source workforce management</div>
	<br>
	<div class='infobox gray hint'>
		<?php if($lic->licenseUsers <= license::FREE_USERS) { ?>
		Wenn Sie diese Software im Produktivbetrieb einsetzen, denken Sie bitte über eine Spende über den <a target='_blank' href='https://github.com/schorschii/masterplan'>Spendenbutton auf GitHub</a> nach, um die Weiterentwicklung zu finanzieren.
		<br><br>
		<?php } ?>
		Kommerzieller Support sowie Weiterentwicklungen sind auf Angebotsbasis möglich. Bitte nehmen Sie <a target='_blank' href='https://georg-sieber.de/?page=impressum'>Kontakt</a> auf.
	</div>
</div>

<?php if($currentUser->superadmin > 0 || count($adminRosters) > 0 || count($userRosters) > 0) { ?>

<div class='contentbox small'>

<?php if($lic->licenseValid) { ?>
	<h2><?php echo htmlspecialchars($lic->licenseCompany); ?></h2>
<?php } ?>

<?php if($currentUser->superadmin > 0) { ?>
	<div class='infobox yellow'>Sie besitzen Superadmin-Rechte!</div>
<?php } ?>

<?php if(count($adminRosters) > 0) { ?>
	<div class='contentbox small'>
		<div class='infobox'>
			Sie besitzen Admin-Rechte für folgende Dienstpläne:
			<ul>
				<?php foreach($adminRosters as $ar) {
					echo '<li>'.htmlspecialchars($ar->roster_title).'</li>';
				} ?>
			</ul>
		</div>
	</div>
<?php } ?>

<?php if(count($userRosters) > 0) { ?>
	<div class='contentbox small'>
		<div class='infobox'>
			Sie sind folgenden Dienstplänen zugeteilt:
			<ul>
				<?php foreach($userRosters as $ar) {
					echo '<li>'.htmlspecialchars($ar->roster_title).'</li>';
				} ?>
			</ul>
		</div>
	</div>
<?php } ?>

</div>

<?php } ?>
