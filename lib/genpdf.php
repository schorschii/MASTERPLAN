<?php

class genpdf {

	private $dbhandle;
	private $plan;
	private $aplan;
	private $pdfhandle;

	function __construct($dbhandle) {
		$this->dbhandle = $dbhandle;
		$this->plan = new plan($dbhandle);
		$this->aplan = new autoplan($dbhandle);
	}

	public function getPdfHandle() {
		return $this->pdfhandle;
	}

	/* static layout definitions */
	private $h1 = 6; // height of title cell
	private $h2 = 4; // height of normal cell
	private $fSize = 9; // normal font size
	private $marginX = 8;
	private $marginY = 4;

	public function createPlanPdf($roster_id, $strWeek) {
		$timeWd1 = strtotime($strWeek.' +0 day');
		$timeWd2 = strtotime($strWeek.' +1 day');
		$timeWd3 = strtotime($strWeek.' +2 day');
		$timeWd4 = strtotime($strWeek.' +3 day');
		$timeWd5 = strtotime($strWeek.' +4 day');
		$timeWd6 = strtotime($strWeek.' +5 day');
		$timeWd7 = strtotime($strWeek.' +6 day');

		$roster = $this->dbhandle->getRoster($roster_id);

		$title = 'Dienstplan '.$roster->title.' Woche '.date('W', strtotime($strWeek));

		$pdf = new fpdf('L','mm','A4');
		$pdf->SetTitle($title);
		$pdf->SetSubject($title);
		$pdf->SetAuthor("Georg Sieber");
		$pdf->SetCreator("MASTERPLAN");
		$pdf->AddPage('L');
		$pdf->SetAutoPageBreak(false, 1);
		$pdf->SetMargins(8,8);
		$pdf->SetXY($this->marginX, $this->marginY);

		//$pdf->Image("assets/logo.png", 8, 8, 50);

		$pdf->SetFont('Arial','B',18);
		$pdf->SetTextColor(0);
		$pdf->Cell(200,9,utf8_decode($title),0);
		$pdf->SetFont('Arial','B',$this->fSize);
		$pdf->SetFillColor(220);
		$w = ($pdf->GetPageWidth()-16)/7;
		$pdf->ln();
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd1)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd2)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd3)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd4)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd5)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd6)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd7)),1,0,'C',true);
		$pdf->ln();
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd1)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd2)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd3)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd4)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd5)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd6)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd7)),1,0,'C',true);
		$pdf->SetFont('Arial','',$this->fSize);
		$pdf->SetFillColor(255,255,255);

		$pdf->ln();
		$x = $pdf->getX();
		$y = $pdf->getY();
		$c1 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd1), $w, $x, $y, 0);
		$c2 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd2), $w, $x+$w*1, $y, 0);
		$c3 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd3), $w, $x+$w*2, $y, 0);
		$c4 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd4), $w, $x+$w*3, $y, 0);
		$c5 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd5), $w, $x+$w*4, $y, 0);
		$c6 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd6), $w, $x+$w*5, $y, 0);
		$c7 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd7), $w, $x+$w*6, $y, 0);
		while($c1!=0 || $c2!=0 || $c3!=0 || $c4!=0 || $c5!=0 || $c6!=0 || $c7!=0) {
			$pdf->addPage('L');
			$c1 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd1), $w, $x, $y, $c1);
			$c2 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd2), $w, $x+$w*1, $y, $c2);
			$c3 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd3), $w, $x+$w*2, $y, $c3);
			$c4 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd4), $w, $x+$w*3, $y, $c4);
			$c5 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd5), $w, $x+$w*4, $y, $c5);
			$c6 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd6), $w, $x+$w*5, $y, $c6);
			$c7 = $this->planPdfServicesDay($pdf, $roster->id, date('Y-m-d',$timeWd7), $w, $x+$w*6, $y, $c7);
		}

		$this->pdfhandle = $pdf;
	}

	private function planPdfServicesDay($pdfhandle, $roster_id, $day, $w, $x, $y, $continueWith) {
		$pdfhandle->setY($y);
		$counter = 0;
		foreach($this->plan->getConsolidatedServicesByRosterAndDay($roster_id, $day) as $s) {
			$pdfhandle->setX($x);

			$counter ++;
			$assignments = $this->dbhandle->getPlannedServicesWithUserByRosterAndServiceAndDay($roster_id, $s->id, $day);
			$assignedResources = $this->dbhandle->getPlannedServiceResourcesByServiceAndDay($s->id, $day);
			$assignedFiles = $this->dbhandle->getPlannedServiceFilesByServiceIdAndDay($s->id, $day);
			$assignedNotes = $this->dbhandle->getPlannedServiceNotes($s->id, $day);

			if($counter < $continueWith) {
				continue;
			}

			if($pdfhandle->getY() > $pdfhandle->getPageHeight()-$this->marginY-(8*count($assignments))) {
				return $counter;
			}

			$pdfhandle->SetFont('Arial','B',$this->fSize);
			$pdfhandle->MultiCell($w,$this->h1,utf8_decode($s->shortname.' ('.$s->start.'-'.$s->end.')'),'LR');
			$pdfhandle->SetFont('Arial','',$this->fSize);
			foreach($assignments as $a) {
				$pdfhandle->setX($x);
				$pdfhandle->MultiCell($w,$this->h2,utf8_decode($a->user_fullname),'LR');
			}
			for($i=count($assignments); $i<$s->employees; $i++) {
				$pdfhandle->setX($x);
				$pdfhandle->SetTextColor(255,0,0);
				$pdfhandle->SetFont('Arial','I',$this->fSize);
				$pdfhandle->SetFillColor(255,255,180);
				$pdfhandle->MultiCell($w,$this->h2,utf8_decode('vakant'),'LR',0,'R',true);
				$pdfhandle->SetTextColor(0);
				$pdfhandle->SetFont('Arial','',$this->fSize);
				$pdfhandle->SetFillColor(255,255,255);
			}
			foreach($assignedResources as $r) {
				$pdfhandle->setX($x);
				$pdfhandle->MultiCell($w,$this->h2,utf8_decode($r->resource_type.": ".$r->resource_title),'LR');
			}
			foreach($assignedFiles as $f) {
				$pdfhandle->setX($x);
				$pdfhandle->MultiCell($w,$this->h2,utf8_decode($f->title),'LR');
			}
			foreach($assignedNotes as $n) {
				$pdfhandle->setX($x);
				$pdfhandle->MultiCell($w,$this->h2,utf8_decode($n->note),'LR');
			}
		}
		return 0;
	}

	public function createUserServicesPdf($roster_id, $strWeek) {
		$timeWd1 = strtotime($strWeek.' +0 day');
		$timeWd2 = strtotime($strWeek.' +1 day');
		$timeWd3 = strtotime($strWeek.' +2 day');
		$timeWd4 = strtotime($strWeek.' +3 day');
		$timeWd5 = strtotime($strWeek.' +4 day');
		$timeWd6 = strtotime($strWeek.' +5 day');
		$timeWd7 = strtotime($strWeek.' +6 day');

		$roster = $this->dbhandle->getRoster($roster_id);

		if($roster_id == -1) {
			$title = 'Alle Mitarbeiter, Woche '.date('W', strtotime($strWeek));
		} else {
			$title = 'Mitarbeiter '.$roster->title.', Woche '.date('W', strtotime($strWeek));
		}

		$pdf = new fpdf('L','mm','A4');
		$pdf->SetTitle($title);
		$pdf->SetSubject($title);
		$pdf->SetAuthor("Georg Sieber");
		$pdf->SetCreator("MASTERPLAN");
		$pdf->AddPage('L');
		$pdf->SetAutoPageBreak(false, 1);
		$pdf->SetMargins(8,8);
		$pdf->SetXY($this->marginX, $this->marginY);

		//$pdf->Image("assets/logo.png", 8, 8, 50);

		$pdf->SetFont('Arial','B',18);
		$pdf->SetTextColor(0);
		$pdf->Cell(200,9,utf8_decode($title),0);
		$pdf->SetFont('Arial','B',$this->fSize);
		$pdf->SetFillColor(220);
		$w = ($pdf->GetPageWidth()-16)/8;
		$pdf->ln();
		$pdf->Cell($w,$this->h1,utf8_decode('Mitarbeiter'),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd1)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd2)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd3)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd4)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd5)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd6)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd7)),1,0,'C',true);
		$pdf->ln();
		$pdf->Cell($w,$this->h1,utf8_decode(''),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd1)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd2)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd3)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd4)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd5)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd6)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd7)),1,0,'C',true);
		$pdf->SetFont('Arial','',$this->fSize);
		$pdf->SetFillColor(255,255,255);

		$pdf->ln();
		$y = $pdf->getY();

		$users = [];
		if($roster_id == -1) $users = $this->dbhandle->getUsers();
		else $users = $this->dbhandle->getUsersByRoster($roster->id);
		foreach($users as $u) {
			if($pdf->getY() > $pdf->getPageHeight()-$this->marginY-10) {
				$pdf->addPage('L');
				$y = $this->marginY;
			}

			$items1 = $this->getUserItemsOfDay($u->id, date('Y-m-d', $timeWd1));
			$items2 = $this->getUserItemsOfDay($u->id, date('Y-m-d', $timeWd2));
			$items3 = $this->getUserItemsOfDay($u->id, date('Y-m-d', $timeWd3));
			$items4 = $this->getUserItemsOfDay($u->id, date('Y-m-d', $timeWd4));
			$items5 = $this->getUserItemsOfDay($u->id, date('Y-m-d', $timeWd5));
			$items6 = $this->getUserItemsOfDay($u->id, date('Y-m-d', $timeWd6));
			$items7 = $this->getUserItemsOfDay($u->id, date('Y-m-d', $timeWd7));
			$maxItems = max(
				count($items1), count($items2), count($items3), count($items4), count($items5), count($items6), count($items7)
			);
			$maxItems = max(1, $maxItems);

			$pdf->setY($y);
			$x1 = $pdf->getX();
			$pdf->Cell($w,$this->h2,utf8_decode($u->fullname),'TL');
			$x = $pdf->getX();
			for($i=0; $i<$maxItems; $i++) {
				$pdf->setX($x1);
				$pdf->Cell($w,$this->h2,'','L',0,'C');
				$pdf->ln();
			}

			$y2 = $y;
			$y2 = max($y2, $this->userServicesPdfServicesDay($pdf, $items1, $maxItems, $w, $x, $y));
			$y2 = max($y2, $this->userServicesPdfServicesDay($pdf, $items2, $maxItems, $w, $x+$w*1, $y));
			$y2 = max($y2, $this->userServicesPdfServicesDay($pdf, $items3, $maxItems, $w, $x+$w*2, $y));
			$y2 = max($y2, $this->userServicesPdfServicesDay($pdf, $items4, $maxItems, $w, $x+$w*3, $y));
			$y2 = max($y2, $this->userServicesPdfServicesDay($pdf, $items5, $maxItems, $w, $x+$w*4, $y));
			$y2 = max($y2, $this->userServicesPdfServicesDay($pdf, $items6, $maxItems, $w, $x+$w*5, $y));
			$y2 = max($y2, $this->userServicesPdfServicesDay($pdf, $items7, $maxItems, $w, $x+$w*6, $y));
			$y = $y2;
		}
		// bottom table line
		for($i=1; $i<=8; $i++) {
			$pdf->Cell($w,$this->h2,'','T');
		}

		$this->pdfhandle = $pdf;
	}

	private function getUserItemsOfDay($user_id, $day) {
		$arr = [];
		foreach($this->dbhandle->getAbsencesByUser($user_id) as $a) {
			if(strtotime($a->start) <= strtotime($day)
			&& strtotime($a->end) >= strtotime($day)) {
				$arr[] = [
					'style' => ($this->aplan->isAbsenceApproved($a) ? 'normal' : 'small'),
					'text' => $a->absent_type_shortname
				];
			}
		}
		foreach($this->dbhandle->getPlannedServicesByUserAndDay($user_id, $day) as $ps) {
			$arr[] = [
				'style' => 'normal',
				'text' => $ps->service_shortname
			];
			$arr[] = [
				'style' => 'small',
				'text' => $ps->service_start.'-'.$ps->service_end
			];
		}
		return $arr;
	}

	private function userServicesPdfServicesDay($pdfhandle, $items, $maxItems, $w, $x, $y) {
		$pdfhandle->setY($y);
		$border = 'TLR';
		$counter = 0;
		foreach($items as $item) {
			$counter ++;

			if($item['style'] == 'small') {
				$pdfhandle->SetTextColor(150);
			} else {
				$pdfhandle->SetTextColor(0);
			}

			$pdfhandle->setX($x);
			$pdfhandle->Cell($w,$this->h2,utf8_decode($item['text']),$border,0,'C');
			$pdfhandle->ln();
			$border = 'LR';
			$pdfhandle->SetTextColor(0);
		}
		for($i=$counter; $i<$maxItems; $i++) {
			$pdfhandle->setX($x);
			$pdfhandle->Cell($w,$this->h2,'',$border,0,'C');
			$pdfhandle->ln();
			$border = 'LR';
		}
		return $pdfhandle->getY();
	}

	public function createFreeUsersPdf($roster_id, $strWeek) {
		$timeWd1 = strtotime($strWeek.' +0 day');
		$timeWd2 = strtotime($strWeek.' +1 day');
		$timeWd3 = strtotime($strWeek.' +2 day');
		$timeWd4 = strtotime($strWeek.' +3 day');
		$timeWd5 = strtotime($strWeek.' +4 day');
		$timeWd6 = strtotime($strWeek.' +5 day');
		$timeWd7 = strtotime($strWeek.' +6 day');

		$roster = $this->dbhandle->getRoster($roster_id);

		if($roster_id == -1) {
			$title = 'Freie Mitarbeiter, Woche '.date('W', strtotime($strWeek));
		} else {
			$title = 'Freie Mitarbeiter '.$roster->title.', Woche '.date('W', strtotime($strWeek));
		}

		$pdf = new fpdf('L','mm','A4');
		$pdf->SetTitle($title);
		$pdf->SetSubject($title);
		$pdf->SetAuthor("Georg Sieber");
		$pdf->SetCreator("MASTERPLAN");
		$pdf->AddPage('L');
		$pdf->SetAutoPageBreak(false, 1);
		$pdf->SetMargins(8,8);
		$pdf->SetXY($this->marginX, $this->marginY);

		//$pdf->Image("assets/logo.png", 8, 8, 50);

		$pdf->SetFont('Arial','B',18);
		$pdf->SetTextColor(0);
		$pdf->Cell(200,9,utf8_decode($title),0);
		$pdf->SetFont('Arial','B',$this->fSize);
		$pdf->SetFillColor(220);
		$w = ($pdf->GetPageWidth()-16)/7;
		$pdf->ln();
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd1)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd2)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd3)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd4)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd5)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd6)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime('%A',$timeWd7)),1,0,'C',true);
		$pdf->ln();
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd1)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd2)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd3)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd4)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd5)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd6)),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT,$timeWd7)),1,0,'C',true);
		$pdf->SetFont('Arial','',$this->fSize);
		$pdf->SetFillColor(255,255,255);

		$pdf->ln();
		$y = $pdf->getY();

		$users = [];
		if($roster_id == -1) $users = $this->dbhandle->getUsers();
		else $users = $this->dbhandle->getUsersByRoster($roster->id);

		$items1 = $this->getFreeUsersOfDay($users, date('Y-m-d', $timeWd1));
		$items2 = $this->getFreeUsersOfDay($users, date('Y-m-d', $timeWd2));
		$items3 = $this->getFreeUsersOfDay($users, date('Y-m-d', $timeWd3));
		$items4 = $this->getFreeUsersOfDay($users, date('Y-m-d', $timeWd4));
		$items5 = $this->getFreeUsersOfDay($users, date('Y-m-d', $timeWd5));
		$items6 = $this->getFreeUsersOfDay($users, date('Y-m-d', $timeWd6));
		$items7 = $this->getFreeUsersOfDay($users, date('Y-m-d', $timeWd7));
		$maxItems = max(
			count($items1), count($items2), count($items3), count($items4), count($items5), count($items6), count($items7)
		);
		$maxItems = max(1, $maxItems);

		$x = $pdf->getX();
		$y2 = $y;
		$y2 = max($y2, $this->freeUsersPdfDay($pdf, $items1, $maxItems, $w, $x, $y));
		$y2 = max($y2, $this->freeUsersPdfDay($pdf, $items2, $maxItems, $w, $x+$w*1, $y));
		$y2 = max($y2, $this->freeUsersPdfDay($pdf, $items3, $maxItems, $w, $x+$w*2, $y));
		$y2 = max($y2, $this->freeUsersPdfDay($pdf, $items4, $maxItems, $w, $x+$w*3, $y));
		$y2 = max($y2, $this->freeUsersPdfDay($pdf, $items5, $maxItems, $w, $x+$w*4, $y));
		$y2 = max($y2, $this->freeUsersPdfDay($pdf, $items6, $maxItems, $w, $x+$w*5, $y));
		$y2 = max($y2, $this->freeUsersPdfDay($pdf, $items7, $maxItems, $w, $x+$w*6, $y));

		// bottom table line
		for($i=1; $i<=7; $i++) {
			$pdf->Cell($w,$this->h2,'','T');
		}

		$this->pdfhandle = $pdf;
	}

	private function getFreeUsersOfDay($users, $day) {
		$arr = [];
		foreach($users as $u) {
			if(count($this->dbhandle->getPlannedServicesByUserAndDay($u->id, $day)) == 0) {
				$arr[] = [
					'style' => 'normal',
					'text' => $u->fullname
				];
			}
		}
		return $arr;
	}

	private function freeUsersPdfDay($pdfhandle, $items, $maxItems, $w, $x, $y) {
		$pdfhandle->setY($y);
		$border = 'TLR';
		$counter = 0;
		foreach($items as $item) {
			$counter ++;

			if($item['style'] == 'small') {
				$pdfhandle->SetTextColor(150);
			} else {
				$pdfhandle->SetTextColor(0);
			}

			$pdfhandle->setX($x);
			$pdfhandle->Cell($w,$this->h2,utf8_decode($item['text']),$border,0,'C');
			$pdfhandle->ln();
			$border = 'LR';
			$pdfhandle->SetTextColor(0);
		}
		for($i=$counter; $i<$maxItems; $i++) {
			$pdfhandle->setX($x);
			$pdfhandle->Cell($w,$this->h2,'',$border,0,'C');
			$pdfhandle->ln();
			$border = 'LR';
		}
		return $pdfhandle->getY();
	}

	public function createAbsencePdf($user_id) {
		$user = $this->dbhandle->getUser($user_id);

		$title = 'Abwesenheiten '.$user->fullname;

		$pdf = new fpdf('L','mm','A4');
		$pdf->SetTitle($title);
		$pdf->SetSubject($title);
		$pdf->SetAuthor("Georg Sieber");
		$pdf->SetCreator("MASTERPLAN");
		$pdf->AddPage('L');
		$pdf->SetAutoPageBreak(false, 1);
		$pdf->SetMargins(8,8);
		$pdf->SetXY($this->marginX, $this->marginY);

		//$pdf->Image("assets/logo.png", 8, 8, 50);

		$pdf->SetFont('Arial','B',18);
		$pdf->SetTextColor(0);
		$pdf->Cell(200,9,utf8_decode($title),0);
		$pdf->SetFont('Arial','B',$this->fSize);
		$pdf->SetFillColor(220,220,220);

		$absences = $this->dbhandle->getFutureAbsencesByUser($user_id);

		$w = ($pdf->GetPageWidth()-16)/4;

		$pdf->ln();
		$pdf->Cell($w,$this->h1,utf8_decode('Kürzel'),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode('Beginn'),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode('Ende'),1,0,'C',true);
		$pdf->Cell($w,$this->h1,utf8_decode('Best./Genehmigt'),1,0,'C',true);

		$pdf->SetFillColor(255,255,255);

		foreach($absences as $a) {
			$approved = $this->aplan->isAbsenceApproved($a);

			$approved_user_text = '';
			if($a->approved1_by_user_id != null)
				$approved_user_text .= ' ('.$this->dbhandle->getUser($a->approved1_by_user_id)->fullname.')';
			if($a->approved2_by_user_id != null)
				$approved_user_text .= ' ('.$this->dbhandle->getUser($a->approved2_by_user_id)->fullname.')';

			$pdf->ln();
			$pdf->Cell($w,$this->h1,utf8_decode($a->absent_type_shortname),1,0,'C',true);
			$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT, strtotime($a->start))),1,0,'C',true);
			$pdf->Cell($w,$this->h1,utf8_decode(strftime(DATE_FORMAT, strtotime($a->end))),1,0,'C',true);
			$pdf->Cell($w,$this->h1,utf8_decode( ($approved ? 'Ja' : 'Nein').htmlspecialchars($approved_user_text) ),1,0,'C',true);
			if(!empty($a->comment)) {
				$pdf->ln();
				$pdf->Cell($w*4,$this->h1,utf8_decode($a->comment),1,0,'C',true);
				$pdf->ln();
			}
		}

		$this->pdfhandle = $pdf;
	}

}
