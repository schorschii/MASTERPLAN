<?php

class genhtml {

	private $dbhandle;
	private $plan;
	private $htmlText;
	private $styleDefinition = '
	<style>
	table.tblMasterplan {
		border-collapse: collapse;
	}
	table.tblMasterplan th,
	table.tblMasterplan td {
		border: 1px solid gray;
		padding: 3px 5px;
	}
	td.vakant {
		color: red;
		background-color: rgba(255,255,220);
	}
	td.header {
		font-weight: bold;
		background-color: rgb(230,230,230);
	}
	tr.center td {
		text-align: center;
	}
	tr.top td {
		vertical-align: top;
	}
	.small {
		color: gray;
		font-size: 90%;
	}
	</style>
	';

	function __construct($dbhandle) {
		$this->dbhandle = $dbhandle;
		$this->plan = new plan($dbhandle);
	}

	public function getHtmlText() {
		return $this->htmlText;
	}

	public function createPlanHtml($roster_id, $strWeek) {
		$timeWd1 = strtotime($strWeek.' +0 day');
		$timeWd2 = strtotime($strWeek.' +1 day');
		$timeWd3 = strtotime($strWeek.' +2 day');
		$timeWd4 = strtotime($strWeek.' +3 day');
		$timeWd5 = strtotime($strWeek.' +4 day');
		$timeWd6 = strtotime($strWeek.' +5 day');
		$timeWd7 = strtotime($strWeek.' +6 day');

		$roster = $this->dbhandle->getRoster($roster_id);

		$html = '<h2>Dienstplan '.htmlspecialchars($roster->title).' Woche '.date('W', strtotime($strWeek)).'</h2>';
		$html.= $this->styleDefinition;
		$html.= '<table class="tblMasterplan">';
		$html.= '<tr>';
		$html.= '<th>'.strftime('%A',$timeWd1).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd2).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd3).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd4).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd5).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd6).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd7).'</th>';
		$html.= '</tr>';
		$html.= '<tr>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd1).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd2).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd3).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd4).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd5).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd6).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd7).'</th>';
		$html.= '</tr>';

		$arrWd1 = $this->planHtmlServicesDay($roster->id, date('Y-m-d', $timeWd1));
		$arrWd2 = $this->planHtmlServicesDay($roster->id, date('Y-m-d', $timeWd2));
		$arrWd3 = $this->planHtmlServicesDay($roster->id, date('Y-m-d', $timeWd3));
		$arrWd4 = $this->planHtmlServicesDay($roster->id, date('Y-m-d', $timeWd4));
		$arrWd5 = $this->planHtmlServicesDay($roster->id, date('Y-m-d', $timeWd5));
		$arrWd6 = $this->planHtmlServicesDay($roster->id, date('Y-m-d', $timeWd6));
		$arrWd7 = $this->planHtmlServicesDay($roster->id, date('Y-m-d', $timeWd7));
		$html.= $this->buildTableRows( [$arrWd1, $arrWd2, $arrWd3, $arrWd4, $arrWd5, $arrWd6, $arrWd7] );

		$html.= '</table>';

		$this->htmlText = $html;
	}

	private function buildTableRows($arrArr) {
		$html = '';
		$maxLines = 0;
		foreach($arrArr as $arr) {
			$maxLines = max($maxLines, count($arr));
		}
		for($i=0; $i<$maxLines; $i++) {
			$html.= '<tr>';
			foreach($arrArr as $arr) {
				if(isset($arr[$i])) {
					$html.= '<td class="'.$arr[$i]['style'].'">'.htmlspecialchars($arr[$i]['text']).'</td>';
				} else {
					$html.= '<td></td>';
				}
			}
			$html.= '</tr>';
		}
		return $html;
	}

	private function planHtmlServicesDay($roster_id, $day) {
		$arr = [];
		foreach($this->plan->getConsolidatedServicesByRosterAndDay($roster_id, $day) as $s) {
			$assignments = $this->dbhandle->getPlannedServicesWithUserByRosterAndServiceAndDay($roster_id, $s->id, $day);
			$arr[] = [
				'text' => $s->shortname.' ('.$s->start.'-'.$s->end.')',
				'style' => 'header'
			];
			foreach($assignments as $a) {
				$arr[] = [
					'text' => htmlspecialchars($a->user_fullname),
					'style' => 'normal'
				];
			}
			for($i=count($assignments); $i<$s->employees; $i++) {
				$arr[] = [
					'text' => 'vakant',
					'style' => 'vakant'
				];
			}
		}
		return $arr;
	}

	public function createUserServicesHtml($roster_id, $strWeek) {
		$timeWd1 = strtotime($strWeek.' +0 day');
		$timeWd2 = strtotime($strWeek.' +1 day');
		$timeWd3 = strtotime($strWeek.' +2 day');
		$timeWd4 = strtotime($strWeek.' +3 day');
		$timeWd5 = strtotime($strWeek.' +4 day');
		$timeWd6 = strtotime($strWeek.' +5 day');
		$timeWd7 = strtotime($strWeek.' +6 day');

		$roster = $this->dbhandle->getRoster($roster_id);

		$html = '';
		if($roster_id == -1) {
			$html.= '<h2>Alle Mitarbeiter, Woche '.date('W', strtotime($strWeek)).'</h2>';
		} else {
			$html.= '<h2>Mitarbeiter '.$roster->title.', Woche '.date('W', strtotime($strWeek)).'</h2>';
		}
		$html.= $this->styleDefinition;
		$html.= '<table class="tblMasterplan">';
		$html.= '<tr>';
		$html.= '<th>'.'Mitarbeiter'.'</th>';
		$html.= '<th>'.strftime('%A',$timeWd1).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd2).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd3).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd4).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd5).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd6).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd7).'</th>';
		$html.= '</tr>';
		$html.= '<tr>';
		$html.= '<th></th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd1).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd2).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd3).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd4).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd5).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd6).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd7).'</th>';
		$html.= '</tr>';

		$users = [];
		if($roster_id == -1) $users = $this->dbhandle->getUsers();
		else $users = $this->dbhandle->getUsersByRoster($roster->id);
		foreach($users as $u) {
			$html.= '<tr class="center">';
			$html.= '<th>'.htmlspecialchars($u->fullname).'</th>';
			$html.= '<td>'.$this->userServicesHtmlServicesDay($u->id, date('Y-m-d', $timeWd1)).'</td>';
			$html.= '<td>'.$this->userServicesHtmlServicesDay($u->id, date('Y-m-d', $timeWd2)).'</td>';
			$html.= '<td>'.$this->userServicesHtmlServicesDay($u->id, date('Y-m-d', $timeWd3)).'</td>';
			$html.= '<td>'.$this->userServicesHtmlServicesDay($u->id, date('Y-m-d', $timeWd4)).'</td>';
			$html.= '<td>'.$this->userServicesHtmlServicesDay($u->id, date('Y-m-d', $timeWd5)).'</td>';
			$html.= '<td>'.$this->userServicesHtmlServicesDay($u->id, date('Y-m-d', $timeWd6)).'</td>';
			$html.= '<td>'.$this->userServicesHtmlServicesDay($u->id, date('Y-m-d', $timeWd7)).'</td>';
			$html.= '</tr>';
		}

		$this->htmlText = $html;
	}

	private function userServicesHtmlServicesDay($user_id, $day) {
		$html = '';
		$absences = $this->dbhandle->getAbsencesByUser($user_id);
		foreach($absences as $a) {
			if(strtotime($a->start) <= strtotime($day)
			&& strtotime($a->end) >= strtotime($day)) {
				$html.= '<div>'.htmlspecialchars($a->absent_type_shortname).'</div>';
			}
		}
		foreach($this->dbhandle->getPlannedServicesByUserAndDay($user_id, $day) as $ps) {
			$html.= '<div>'.htmlspecialchars($ps->service_shortname).'</div>';
			$html.= '<div class="small">'.htmlspecialchars($ps->service_start.'-'.$ps->service_end).'</div>';
		}
		return $html;
	}

	public function createFreeUsersHtml($roster_id, $strWeek) {
		$timeWd1 = strtotime($strWeek.' +0 day');
		$timeWd2 = strtotime($strWeek.' +1 day');
		$timeWd3 = strtotime($strWeek.' +2 day');
		$timeWd4 = strtotime($strWeek.' +3 day');
		$timeWd5 = strtotime($strWeek.' +4 day');
		$timeWd6 = strtotime($strWeek.' +5 day');
		$timeWd7 = strtotime($strWeek.' +6 day');

		$roster = $this->dbhandle->getRoster($roster_id);

		$html = '';
		if($roster_id == -1) {
			$html.= '<h2>Freie Mitarbeiter, Woche '.date('W', strtotime($strWeek)).'</h2>';
		} else {
			$html.= '<h2>Freie Mitarbeiter '.$roster->title.', Woche '.date('W', strtotime($strWeek)).'</h2>';
		}
		$html.= $this->styleDefinition;
		$html.= '<table class="tblMasterplan">';
		$html.= '<tr>';
		$html.= '<th>'.strftime('%A',$timeWd1).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd2).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd3).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd4).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd5).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd6).'</th>';
		$html.= '<th>'.strftime('%A',$timeWd7).'</th>';
		$html.= '</tr>';
		$html.= '<tr>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd1).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd2).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd3).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd4).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd5).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd6).'</th>';
		$html.= '<th>'.strftime(DATE_FORMAT,$timeWd7).'</th>';
		$html.= '</tr>';

		$users = [];
		if($roster_id == -1) $users = $this->dbhandle->getUsers();
		else $users = $this->dbhandle->getUsersByRoster($roster->id);
		$html.= '<tr class="top">';
		$html.= '<td>'.$this->freeUsersOnDay($users, date('Y-m-d', $timeWd1)).'</td>';
		$html.= '<td>'.$this->freeUsersOnDay($users, date('Y-m-d', $timeWd2)).'</td>';
		$html.= '<td>'.$this->freeUsersOnDay($users, date('Y-m-d', $timeWd3)).'</td>';
		$html.= '<td>'.$this->freeUsersOnDay($users, date('Y-m-d', $timeWd4)).'</td>';
		$html.= '<td>'.$this->freeUsersOnDay($users, date('Y-m-d', $timeWd5)).'</td>';
		$html.= '<td>'.$this->freeUsersOnDay($users, date('Y-m-d', $timeWd6)).'</td>';
		$html.= '<td>'.$this->freeUsersOnDay($users, date('Y-m-d', $timeWd7)).'</td>';
		$html.= '</tr>';

		$this->htmlText = $html;
	}

	private function freeUsersOnDay($users, $day) {
		$html = '';
		foreach($users as $u) {
			if(count($this->dbhandle->getPlannedServicesByUserAndDay($u->id, $day)) == 0) {
				$html.= '<div>'.htmlspecialchars($u->fullname).'</div>';
			}
		}
		return $html;
	}

}
