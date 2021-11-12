<?php
// rights check
if(!isset($currentUser)) die();
$adminRosters = $db->getUserRostersAdmin($currentUser->id);
$userRosters = $db->getUserRosters($currentUser->id);
?>

<div class='contentbox small'>
	<img id='logo' src='img/logo.png'>
	<div class='start subtitle'><?php LANG['app_subtitle']; ?></div>
	<br>
	<div class='infobox gray hint'>
		<?php if($lic->licenseUsers <= license::FREE_USERS) { ?>
			<?php echo LANG['donation_note']; ?>
			<br><br>
		<?php } ?>
		<?php echo LANG['commercial_support_note']; ?>
	</div>
</div>

<?php if($currentUser->superadmin > 0 || count($adminRosters) > 0 || count($userRosters) > 0) { ?>

<div class='contentbox small'>

<?php if($lic->licenseValid) { ?>
	<h2><?php echo htmlspecialchars($lic->licenseCompany); ?></h2>
<?php } ?>

<?php if($currentUser->superadmin > 0) { ?>
	<div class='infobox yellow'><?php echo LANG['you_have_superadmin_rights']; ?></div>
<?php } ?>

<?php if(count($adminRosters) > 0) { ?>
	<div class='contentbox small'>
		<div class='infobox'>
			<?php echo LANG['you_have_admin_rights_for_following_rosters']; ?>
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
			<?php echo LANG['you_are_assigned_to_the_following_rosters']; ?>
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
