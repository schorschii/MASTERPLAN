<?php
$info = null;
$infoclass = null;

// rights check
if(!isset($currentUser)) die();
if($currentUser->superadmin == 0) {
	die('<div class="infobox red">Sie benötigen Superadmin-Berechtigungen um diese Seite aufzurufen</div>');
}

$user = null;
if(isset($_POST['user'])) {
	$user = $db->getUser($_POST['user']);
}

if($user == null) {
	die('<div class="infobox red">Benutzer nicht gefunden</div>');
}

if(!empty($_POST['action'])) {
	if($_POST['action'] == 'addUserConstraint') {
		if(isset($_POST['service'])) {
			if($_POST['wd1'] == '0'
			&& $_POST['wd2'] == '0'
			&& $_POST['wd3'] == '0'
			&& $_POST['wd4'] == '0'
			&& $_POST['wd5'] == '0'
			&& $_POST['wd6'] == '0'
			&& $_POST['wd7'] == '0') {
				$info = "Beschränkung nicht gespeichert: Sie müssen mindestens einen Wochentag auswählen, sonst ist die Beschränkung nutzlos.";
				$infoclass = "yellow";
			} elseif($db->updateUserConstraint(
				null,
				$user->id,
				(empty($_POST['service']) ? null : $_POST['service']),
				$_POST['wd1'],
				$_POST['wd2'],
				$_POST['wd3'],
				$_POST['wd4'],
				$_POST['wd5'],
				$_POST['wd6'],
				$_POST['wd7'],
				$_POST['comment']
			)) {
				$info = "Beschränkung gespeichert";
				$infoclass = "green";
			} else {
				$info = "Beschränkung konnte nicht gespeichert werden: ".$db->getLastStatement()->error;
				$infoclass = "red";
			}
		}
	}
	elseif($_POST['action'] == 'removeUserConstraint') {
		if($db->removeUserConstraint($_POST['id'])) {
			$info = "Beschränkung entfernt";
			$infoclass = "green";
		} else {
			$info = "Beschränkung konnte nicht entfernt werden";
			$infoclass = "green";
		}
	}
}
?>
<div class="contentbox small">
	<h2>Beschränkung hinzufügen</h2>

	<?php if($info != null) { ?>
		<div class="infobox <?php echo $infoclass; ?>"><?php echo htmlspecialchars($info); ?></div>
	<?php } ?>

	<a class='button' href='?view=editUser&id=<?php echo $user->id; ?>'><img src='img/user.svg'>&nbsp;<?php echo htmlspecialchars($user->fullname); ?></a>

	<form method="POST">
		<input type='hidden' name='action' value='addUserConstraint'>
		<input type="hidden" name="user" value="<?php echo $user->id; ?>">
		<div class="margintop">
			Dieser Mitarbeiter darf am:
			<div>
				<label>
					<input type="hidden" name="wd1" value="0">
					<input type="checkbox" name="wd1" value="1">Mo
				</label>
				<label>
					<input type="hidden" name="wd2" value="0">
					<input type="checkbox" name="wd2" value="1">Di
				</label>
				<label>
					<input type="hidden" name="wd3" value="0">
					<input type="checkbox" name="wd3" value="1">Mi
				</label>
				<label>
					<input type="hidden" name="wd4" value="0">
					<input type="checkbox" name="wd4" value="1">Do
				</label>
				<label>
					<input type="hidden" name="wd5" value="0">
					<input type="checkbox" name="wd5" value="1">Fr
				</label>
				<label>
					<input type="hidden" name="wd6" value="0">
					<input type="checkbox" name="wd6" value="1">Sa
				</label>
				<label>
					<input type="hidden" name="wd7" value="0">
					<input type="checkbox" name="wd7" value="1">So
				</label>
			</div>
		</div>
		<div class="margintop">
			nicht für:
			<select name="service">
				<option value="0">ALLE DIENSTE</option>
				<?php
				foreach($db->getUserRosters($user->id) as $ur) {
					echo "<optgroup label='".htmlspecialchars($ur->roster_title)."'>";
					foreach($db->getServicesFromRoster($ur->roster_id) as $s) {
						echo "<option value='".$s->id."'>".htmlspecialchars($s->shortname)." ".htmlspecialchars($s->title)."</option>";
					}
					echo "</optgroup>";
				}
				?>
			</select>
		</div>
		<div class="margintop">
			eingesetzt werden.
		</div>
		<div class="margintop">
			<input class="fullwidth" type="text" name="comment" placeholder="Kommentar zu dieser Beschränkung (optional)">
		</div>

		<div class="margintop">
			<button class="fullwidth"><img src='img/ok.svg'>&nbsp;Speichern</button>
		</div>
	</form>
</div>

<div class="contentbox small">
		<h2>Aktive Beschränkungen</h2>
		<?php
		$constraints = $db->getUserConstraints($user->id);
		if(sizeof($constraints) == 0) {
			echo "<div class='infobox'>Keine Beschränkungen definiert</div>";
		} else {
			echo "<table class='data'>"
				."<tr><th>Dienst</th><th>Mo</th><th>Di</th><th>Mi</th><th>Do</th><th>Fr</th><th>Sa</th><th>So</th><th>Aktion</th></tr>";
			foreach($constraints as $c) {
				echo "<tr>"
					."<td>"
					.(($c->comment=='') ? "" : "<img src='img/info-gray.svg' title='".htmlspecialchars($c->comment)."'>&nbsp;")
					.($c->shortname==null ? "ALLE DIENSTE" : htmlspecialchars($c->shortname))
					."</td>"
					."<td class='wrapcontent center'>".($c->wd1 ? "&#10005;" : "")."</td>"
					."<td class='wrapcontent center'>".($c->wd2 ? "&#10005;" : "")."</td>"
					."<td class='wrapcontent center'>".($c->wd3 ? "&#10005;" : "")."</td>"
					."<td class='wrapcontent center'>".($c->wd4 ? "&#10005;" : "")."</td>"
					."<td class='wrapcontent center'>".($c->wd5 ? "&#10005;" : "")."</td>"
					."<td class='wrapcontent center'>".($c->wd6 ? "&#10005;" : "")."</td>"
					."<td class='wrapcontent center'>".($c->wd7 ? "&#10005;" : "")."</td>"
					."<td class='wrapcontent'>"
					. "<form method='POST' onsubmit='return confirm(\"Beschränkung wirklich entfernen?\")'>"
					. "<input type='hidden' name='action' value='removeUserConstraint'>"
					. "<input type='hidden' name='user' value='".$user->id."'>"
					. "<input type='hidden' name='id' value='".$c->id."'>"
					. "<button title='Entfernen'><img src='img/delete.svg'></button>"
					. "</form>"
					."</td>"
					."</tr>";
			}
			echo "</table>";
		} ?>
</div>
