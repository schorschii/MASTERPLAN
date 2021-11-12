<?php
function genURL($view, $roster_id) {
	global $currentUser;
	$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
	$pathParts = explode('/', $_SERVER['REQUEST_URI']);
	array_splice($pathParts, -2);
	$path = implode('/', $pathParts) . '/api/ics.php';
	$param = '?user='.$currentUser->id.'&auth='.md5($currentUser->password).'&view='.$view;
	if($roster_id != -1) $param .= '&roster='.$roster_id;
	$server = $_SERVER['HTTP_HOST'];
	$link = "$protocol://$server$path$param";
	return $link;
}
?>

<script>
	function displayURL(strTitle, strUrl) {
		urlbox.style.display = 'block';
		calTitle.innerHTML = strTitle;
		calUrl.value = strUrl;
	}
</script>

<div class="contentbox small">
	<h2><?php echo LANG['services_as_calendar']; ?></h2>
	<div class="infobox gray">
		<?php echo LANG['services_as_calendar_description']; ?>
	</div>
	<h3><?php echo LANG['please_select_calendar']; ?></h3>
	<button class="fullwidth" onclick="displayURL('<?php echo LANG['my_services']; ?>', '<?php echo genURL("userServices", -1); ?>')"><?php echo LANG['my_services']; ?></button>
	<?php foreach($db->getUserRosters($currentUser->id) as $r) { ?>
		<button class="fullwidth" onclick="displayURL('<?php echo htmlspecialchars($r->roster_title); ?>', '<?php echo genURL("roster", $r->roster_id); ?>')"><?php echo LANG['all_services_of']; ?> »<?php echo htmlspecialchars($r->roster_title); ?>«</button>
	<?php } ?>
</div>

<div class="contentbox small" id="urlbox" style="display:none">
	<h2><?php echo LANG['url_to_calendar']; ?></h2>
	<div class="infobox gray">
		<?php echo LANG['please_copy_url_into_client']; ?>
	</div>
	<div class="infobox yellow">
		<?php echo LANG['url_to_calendar_description']; ?>
	</div>
	<h3 id="calTitle"><?php echo LANG['calendar_name']; ?></h3>
	<input type="text" id="calUrl" readonly="true">
	<button class="fullwidth" onclick="calUrl.select();document.execCommand('copy');"><?php echo LANG['copy_to_clipboard']; ?></button>
</div>
