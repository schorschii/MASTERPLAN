<?php

class ics {

	private static function getIcalDate($time, $incl_time = true) {
		// gmdate converts local time to UTC
		// append "Z" to indicate time is in UTC - for Outlook
		return $incl_time ? gmdate('Ymd\THis', $time)."Z" : date('Ymd', $time);
	}

	public static function sendIcsMail($fromName, $fromAddress, $toAddress, $subject, $description, $icsBody) {
		// Create Email Header
		$mime_boundary = "----Meeting Booking----".MD5(TIME());
		$headers  = "From: ".$fromName." <".$fromAddress.">\n";
		$headers .= "Reply-To: ".$fromName." <".$fromAddress.">\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: multipart/alternative; charset=UTF-8; boundary=\"".$mime_boundary."\"\n";
		$headers .= "Content-class: urn:content-classes:calendarmessage\n";

		// Create Email Body
		$message  = "--".$mime_boundary."\r\n";
		$message .= "Content-Type: text/html; charset=UTF-8\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		$message .= "<html>"
			."<head>"
			."<style>body { font-family: sans-serif; }</style>"
			."</head>"
			."<body>".htmlspecialchars($description)."</body>"
			."</html>\n";
		$message .= "--".$mime_boundary."\r\n";
		$message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST'."\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		$message .= $icsBody;

		// Send Email
		return mail($toAddress, $subject, $message, $headers);
	}

	public static function compileIcsBody(
		$eventUid, $domain, $fromName, $fromAddress, $toName, $toAddress,
		$startTime, $endTime, $subject, $description, $location, $cancel = false
	) {
		$strSequence = 'SEQUENCE:1'."\r\n";
		$strCancel = '';
		$strMethod = 'METHOD:REQUEST'."\r\n";
		if($cancel) {
			$strSequence = 'SEQUENCE:2'."\r\n";
			$strCancel = 'STATUS:CANCELLED'."\r\n";
			$strMethod = 'METHOD:CANCEL'."\r\n";
		}
		$ics = 'BEGIN:VCALENDAR'."\r\n" ###############
			.'PRODID:-//MASTERPLAN//GEORG SIEBER//DE'."\r\n"
			.'VERSION:2.0'."\r\n"
			.$strMethod
			.'CALSCALE:GREGORIAN'."\r\n"
			.'BEGIN:VEVENT'."\r\n" ##########
			.'ORGANIZER;CN="'.$fromName.'":MAILTO:'.$fromAddress."\r\n"
			.'ATTENDEE;CN="'.$toName.'";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:'.$toAddress."\r\n"
			.'LAST-MODIFIED:'.self::getIcalDate(strtotime("now"))."\r\n"
			.'UID:'.intval($eventUid)."@".$domain."\r\n"
			.'DTSTAMP:'.self::getIcalDate(strtotime("now"))."\r\n"
			.'DTSTART:'.self::getIcalDate($startTime)."\r\n"
			.'DTEND:'.self::getIcalDate($endTime)."\r\n"
			.'DESCRIPTION:'.$description."\r\n"
			.'TRANSP:OPAQUE'."\r\n"
			.$strSequence
			.'SUMMARY:'.$subject."\r\n"
			.'LOCATION:'.$location."\r\n"
			.'CLASS:PUBLIC'."\r\n"
			.'PRIORITY:5'."\r\n"
			.$strCancel
			.'BEGIN:VALARM'."\r\n" #####
			.'TRIGGER:-PT15M'."\r\n"
			.'ACTION:DISPLAY'."\r\n"
			.'DESCRIPTION:Reminder'."\r\n"
			.'END:VALARM'."\r\n" #####
			.'END:VEVENT'."\r\n" ##########
			.'END:VCALENDAR'."\r\n"; ###############
		return $ics;
	}
}
