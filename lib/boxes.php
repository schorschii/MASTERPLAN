<?php

class boxes {

	public static function echoService($service, $day, $db, $showFiles=true) {
		/// service meta data ///
		$extraClass = ''; if(color::isDarkBg($service->color)) $extraClass = 'darkbg';
		echo '<div class="service '.$extraClass.'" '
			.'title="'.htmlspecialchars($service->title)."\n".'Ort: '.htmlspecialchars($service->location).'" '
			.'style="background-color:'.$service->color.'">';
		echo '<div class="title">'.htmlspecialchars($service->shortname).'</div>';
		echo '<div class="subtitle">'.htmlspecialchars($service->start).'-'.htmlspecialchars($service->end).'</div>';

		/// assigned resources ///
		$assignmentsResources = $db->getPlannedServiceResourcesByServiceAndDay($service->id, $day);
		foreach($assignmentsResources as $r) {
			$extraClass = '';
			if(color::isDarkBg($r->resource_color)) $extraClass = 'darkbg';
			echo '<div class="servicefiles">';
			echo '<span class="resourcesmall '.$extraClass.'" style="background-color:'.htmlspecialchars($r->resource_color).'">';
			echo '<img src="'.htmlspecialchars($r->resource_icon).'">&nbsp;'.htmlspecialchars($r->resource_title);
			echo '</span>';
			echo '</div>';
		}

		/// service files ///
		if($showFiles) {
			$files = $db->getPlannedServiceFilesByServiceIdAndDay($service->id, $day);
			foreach($files as $file) {
				echo '<div class="servicefiles">';
				echo '<a href="fileprovider.php?type=servicefile&id='.$file->id.'" target="_blank" title="'.htmlspecialchars($file->title).'">'
					.'<img src="img/file.svg">&nbsp;'
					.nl2br(htmlspecialchars(tools::shortText($file->title,12)))
					.'</a>';
				echo '</div>';
			}
		}

		/// service note ///
		$notes = $db->getPlannedServiceNotes($service->id, $day);
		if(isset($notes[0]))
			echo '<div class="servicenote" title="'.htmlspecialchars($notes[0]->note).'">'.nl2br(htmlspecialchars(tools::shortText($notes[0]->note))).'</div>';

		echo '</div>';
	}

	public static function echoAbsence($absence, $db) {
		$aplan = new autoplan($db);
		$approved = $aplan->isAbsenceApproved($absence);

		$extraClass = '';
		if(color::isDarkBg($absence->absent_type_color)) $extraClass .= ' darkbg';
		if(!$approved) $extraClass .= ' pending';

		echo '<div class="service '.$extraClass.'" '
			.'title="'.htmlspecialchars($absence->comment).'" '
			.'style="background-color:'.$absence->absent_type_color.'">';
		echo '<div class="title">'.htmlspecialchars($absence->absent_type_shortname).'</div>';
		if(!$approved) echo '<div class="subtitle">'.'Freigabe ausstehend'.'</div>';
		echo '<div class="subtitle">'.htmlspecialchars(tools::shortText($absence->comment)).'</div>';
		echo '</div>';
	}

	public static function echoUser($user) {
		$extraClass = ''; if(color::isDarkBg($user->color)) $extraClass = 'darkbg';
		echo '<div class="assigneduser '.$extraClass.'" '
			.'style="background-color:'.$user->color.'">'
			.htmlspecialchars(trim($user->fullname)!='' ? $user->fullname : $user->login)
			.'</div>';
	}

}
