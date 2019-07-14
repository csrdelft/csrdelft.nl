<?php

namespace CsrDelft\view\agenda;

use CsrDelft\model\agenda\AgendaModel;

class AgendaMaandView extends AgendaView {

	private $jaar;
	private $maand;

	public function __construct(
		AgendaModel $agenda,
		$jaar,
		$maand
	) {
		parent::__construct($agenda);
		$this->jaar = $jaar;
		$this->maand = $maand;
		$this->titel = 'Maandoverzicht voor ' . strftime('%B %Y', strtotime($this->jaar . '-' . $this->maand . '-01'));
	}

	public function getTitel() {
		return 'Agenda - ' . $this->titel;
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs()
			. '<li class="breadcrumb-item">' . $this->getDropDownYear() . '</li>'
			. '<li class="breadcrumb-item">' . $this->getDropDownMonth() . '</li>';
	}

	private function getDropDownYear() {
		$dropdown = '<select onchange="location.href=this.value;">';
		$minyear = $this->jaar - 5;
		$maxyear = $this->jaar + 5;
		for ($year = $minyear; $year <= $maxyear; $year++) {
			$dropdown .= '<option value="/agenda/maand/' . $year . '/' . $this->maand . '"';
			if ($year == $this->jaar) {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . $year . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

	private function getDropDownMonth() {
		$dropdown = '<select onchange="location.href=this.value;">';
		for ($month = 1; $month <= 12; $month++) {
			$dropdown .= '<option value="/agenda/maand/' . $this->jaar . '/' . $month . '"';
			if ($month == $this->maand) {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . strftime('%B', strtotime($this->jaar . '-' . $month . '-01')) . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

	public function view() {
		$cur = strtotime($this->jaar . '-' . $this->maand . '-01');
		$this->smarty->assign('datum', $cur);
		$this->smarty->assign('weken', $this->model->getItemsByMaand($this->jaar, $this->maand));

		// URL voor vorige maand
		$urlVorige = '/agenda/maand/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar - 1) . '/12';
		} else {
			$urlVorige .= $this->jaar . '/' . ($this->maand - 1);
		}
		$this->smarty->assign('urlVorige', $urlVorige);
		$this->smarty->assign('prevMaand', strftime('%B', strtotime('-1 Month', $cur)));

		// URL voor volgende maand
		$urlVolgende = '/agenda/maand/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar + 1) . '/1';
		} else {
			$urlVolgende .= $this->jaar . '/' . ($this->maand + 1);
		}
		$this->smarty->assign('urlVolgende', $urlVolgende);
		$this->smarty->assign('nextMaand', strftime('%B', strtotime('+1 Month', $cur)));

		$this->smarty->display('agenda/maand.tpl');
	}

}
