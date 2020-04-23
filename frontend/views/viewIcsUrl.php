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
	<h2>Dienste als Kalender einbinden</h2>
	<div class="infobox gray">
		Mit diesem Tool können Sie Kalendar-URLs generieren, die Sie als zusätzliche Kalender z.B. in Thunderbird oder Outlook einbinden können.
	</div>
	<h3>Bitte wählen Sie einen Kalender aus</h3>
	<button class="fullwidth" onclick="displayURL('Meine Dienste', '<?php echo genURL("userServices", -1); ?>')">Meine Dienste</button>
	<?php foreach($db->getUserRosters($currentUser->id) as $r) { ?>
		<button class="fullwidth" onclick="displayURL('<?php echo htmlspecialchars($r->roster_title); ?>', '<?php echo genURL("roster", $r->roster_id); ?>')">Alle Dienste aus »<?php echo htmlspecialchars($r->roster_title); ?>«</button>
	<?php } ?>
</div>

<div class="contentbox small" id="urlbox" style="display:none">
	<h2>URL zum Kalender</h2>
	<div class="infobox gray">
		Bitte kopieren Sie die angezeigte URL und fügen Sie sie in Ihrem Client ein.
	</div>
	<div class="infobox yellow">
		Achtung: jeder, der diese URL kennt, hat Lesezugriff auf den Kalender. Bitte bewahren Sie die URL sicher auf.
	</div>
	<h3 id="calTitle">Kalender-Name</h3>
	<input type="text" id="calUrl" readonly="true">
	<button class="fullwidth" onclick="calUrl.select();document.execCommand('copy');">In die Zwischenablage kopieren</button>
</div>
