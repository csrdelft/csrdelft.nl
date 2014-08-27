<?php

require_once 'MVC/controller/AgendaController.class.php';
require_once 'maalcie/model/MaaltijdenModel.class.php';
require_once 'maalcie/model/CorveeTakenModel.class.php';

/**
 * AgendaModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De Agenda bevat alle Agendeerbare objecten die voorkomen in de webstek.
 */
class AgendaModel extends PersistenceModel {

	const orm = 'AgendaItem';

	protected static $instance;

	public function getAllAgendeerbaar($van, $tot, $ical = false) {
		$result = array();

		if (!is_int($van)) {
			throw new Exception('Invalid timestamp: $van getAllAgendeerbaar()');
		}
		if (!is_int($tot)) {
			throw new Exception('Invalid timestamp: $tot getAllAgendeerbaar()');
		}

		// AgendaItems
		$items = $this->find('eind_moment >= ? AND begin_moment <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)), 'begin_moment ASC, titel ASC');
		foreach ($items as $item) {
			if ($item->magBekijken($ical)) {
				$result[] = $item;
			}
		}

		// Maaltijden
		if (LidInstellingen::get('agenda', 'toonMaaltijden') === 'ja') {
			$result = array_merge($result, MaaltijdenModel::getMaaltijdenVoorAgenda($van, $tot));
		}

		// CorveeTaken
		if (LidInstellingen::get('agenda', 'toonCorvee') === 'iedereen') {
			$result = array_merge($result, CorveeTakenModel::getTakenVoorAgenda($van, $tot, true));
		} elseif (LidInstellingen::get('agenda', 'toonCorvee') === 'eigen') {
			$result = array_merge($result, CorveeTakenModel::getTakenVoorAgenda($van, $tot, false));
		}

		// Verjaardagen
		if (LidInstellingen::get('agenda', 'toonVerjaardagen') === 'ja') {
			//Verjaardagen. Omdat Lid-objectjes eigenlijk niet Agendeerbaar, maar meer iets als
			//PeriodiekAgendeerbaar zijn, maar we geen zin hebben om dat te implementeren,
			//doen we hier even een vieze hack waardoor het wel soort van werkt.
			$GLOBALS['agenda_jaar'] = date('Y', $van);
			$GLOBALS['agenda_maand'] = date('m', ($van + $tot) / 2);

			$result = array_merge($result, Lid::getVerjaardagen($van, $tot, 0, $ical));
		}

		// Sorteren
		usort($result, array('AgendaModel', 'vergelijkAgendeerbaars'));

		return $result;
	}

	/**
	 * Vergelijkt twee Agendeerbaars op beginMoment t.b.v. sorteren.
	 */
	public static function vergelijkAgendeerbaars(Agendeerbaar $foo, Agendeerbaar $bar) {
		if ($foo->getBeginMoment() == $bar->getBeginMoment()) {
			return 0;
		}
		return ($foo->getBeginMoment() > $bar->getBeginMoment()) ? 1 : -1;
	}

	public function getAgendaItem($itemId) {
		return $this->retrieveByPrimaryKey(array($itemId));
	}

	public function getICalendarItems() {
		return $this->getAllAgendeerbaar(strtotime('-1 year'), strtotime('+1 year'), true);
	}

	public function getItemsByDay($jaar, $maand, $dag) {
		$van = mktime(0, 0, 0, $maand, $dag, $jaar);
		$tot = mktime(0, 0, 0, $maand, $dag + 1, $jaar);

		return $this->getAllAgendeerbaar($van, $tot);
	}

	public function getItemsByMaand($jaar, $maand) {
		// Zondag van de eerste week van de maand uitrekenen
		$startMoment = mktime(0, 0, 0, $maand, 1, $jaar);
		if (date('w', $startMoment) != 0) {
			$startMoment = strtotime('last Sunday', $startMoment);
		}

		// Zaterdag van de laatste week van de maand uitrekenen
		$eindMoment = mktime(0, 0, 0, $maand, 1, $jaar);
		$eindMoment = strtotime('+1 month', $eindMoment) - 1;
		if (date('w', $eindMoment) != 6) {
			$eindMoment = strtotime('next Saturday', $eindMoment);
		}

		// Array met weken en dagen maken
		$cur = $startMoment;
		$agenda = array();
		while ($cur <= $eindMoment) {
			$week = getWeekNumber($cur);
			$dag = date('d', $cur);
			$agenda[$week][$dag]['datum'] = $cur;
			$agenda[$week][$dag]['items'] = array();

			$cur = strtotime('+1 day', $cur);
		}

		// Items toevoegen aan het array
		$items = $this->getAllAgendeerbaar($startMoment, $eindMoment);
		foreach ($items as $item) {
			$week = getWeekNumber($item->getBeginMoment());
			$dag = date('d', $item->getEindMoment());
			$agenda[$week][$dag]['items'][] = $item;
		}

		return $agenda;
	}

	/**
	 * Zoek in de activiteiten (titel en beschrijving) van vandaag
	 * naar het woord $woord, geef de eerste terug.
	 */
	public function zoekWoordAgenda($woord) {
		foreach ($this->getItemsByDay(date('Y'), date('m'), date('d')) as $item) {
			if (stristr($item->getTitel(), $woord) !== false OR stristr($item->getBeschrijving(), $woord) !== false) {
				return $item;
			}
		}
		return null;
	}

	public function newAgendaItem($datum) {
		$item = new AgendaItem();
		if (!preg_match('/^[0-9]{4}\-[0-9]{1,2}-[0-9]{1,2}$/', $datum)) {
			$datum = strtotime('Y-m-d');
		}
		$item->begin_moment = getDateTime(strtotime($datum) + 72000);
		$item->eind_moment = getDateTime(strtotime($datum) + 79200);
		$item->rechten_bekijken = Instellingen::get('agenda', 'standaard_zichtbaar_rechten');
		return $item;
	}

	public function removeAgendaItem($aid) {
		$rowcount = $this->deleteByPrimaryKey(array($aid));
		if ($rowcount !== 1) {
			throw new Exception('Agenda-item verwijderen mislukt');
		}
	}

}
