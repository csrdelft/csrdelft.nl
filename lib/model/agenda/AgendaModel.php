<?php

namespace CsrDelft\model\agenda;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\agenda\AgendaItem;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\entity\groepen\ActiviteitSoort;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\VerjaardagenModel;
use CsrDelft\Orm\PersistenceModel;

/**
 * AgendaModel.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De Agenda bevat alle Agendeerbare objecten die voorkomen in de webstek.
 */
class AgendaModel extends PersistenceModel {

	const ORM = AgendaItem::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'begin_moment ASC, titel ASC';

	/**
	 * @var AgendaVerbergenModel
	 */
	private $agendaVerbergenModel;

	/**
	 * @var ActiviteitenModel
	 */
	private $activiteitenModel;

	/**
	 * @var CorveeTakenModel
	 */
	private $corveeTakenModel;

	/**
	 * @var MaaltijdenModel
	 */
	private $maaltijdenModel;

	/**
	 * AgendaModel constructor.
	 * @param AgendaVerbergenModel $agendaVerbergenModel
	 * @param ActiviteitenModel $activiteitenModel
	 * @param CorveeTakenModel $corveeTakenModel
	 * @param MaaltijdenModel $maaltijdenModel
	 */
	protected function __construct(
		AgendaVerbergenModel $agendaVerbergenModel,
		ActiviteitenModel $activiteitenModel,
		CorveeTakenModel $corveeTakenModel,
		MaaltijdenModel $maaltijdenModel
	) {
		parent::__construct();


		$this->agendaVerbergenModel = $agendaVerbergenModel;
		$this->activiteitenModel = $activiteitenModel;
		$this->corveeTakenModel = $corveeTakenModel;
		$this->maaltijdenModel = $maaltijdenModel;
	}

	/**
	 * @param integer $van
	 * @param integer $tot
	 * @param bool $ical
	 * @param bool $zijbalk
	 * @return Agendeerbaar[]
	 * @throws CsrException
	 */
	public function getAllAgendeerbaar($van, $tot, $ical = false, $zijbalk = false) {
		$result = array();

		if (!is_int($van)) {
			throw new CsrException('Invalid timestamp: $van getAllAgendeerbaar()');
		}
		if (!is_int($tot)) {
			throw new CsrException('Invalid timestamp: $tot getAllAgendeerbaar()');
		}

		// AgendaItems
		$begin_moment = date('Y-m-d', $van);
		$eind_moment = date('Y-m-d', $tot);
		/** @var AgendaItem[] $items */
        $items = $this->find('(begin_moment >= ? AND begin_moment < ? + INTERVAL 1 DAY) OR (eind_moment >= ? AND eind_moment < ? + INTERVAL 1 DAY)', array($begin_moment, $eind_moment, $begin_moment, $eind_moment));
		foreach ($items as $item) {
			if ($item->magBekijken($ical)) {
				$result[] = $item;
			}
		}

		$auth = ($ical ? AuthenticationMethod::getTypeOptions() : null);

		// Activiteiten
		$activiteiten = $this->activiteitenModel->find('in_agenda = TRUE AND (
		    (begin_moment >= ? AND begin_moment <= ?) OR (eind_moment >= ? AND eind_moment <= ?)
		  )', array($begin_moment, $eind_moment, $begin_moment, $eind_moment));
		foreach ($activiteiten as $activiteit) {
			// Alleen bekijken in agenda (leden bekijken mag dus niet)
			if (in_array($activiteit->soort, [ActiviteitSoort::Extern, ActiviteitSoort::OWee, ActiviteitSoort::IFES]) OR $activiteit->mag(AccessAction::Bekijken, $auth)) {
				$result[] = $activiteit;
			}
		}

		// Maaltijden
		if (LidInstellingenModel::get('agenda', 'toonMaaltijden') === 'ja') {
			$result = array_merge($result, $this->maaltijdenModel->getMaaltijdenVoorAgenda($van, $tot));
		}

		// CorveeTaken
		if (LidInstellingenModel::get('agenda', 'toonCorvee') === 'iedereen') {
			$result = array_merge($result, $this->corveeTakenModel->getTakenVoorAgenda($van, $tot, true));
		} elseif (LidInstellingenModel::get('agenda', 'toonCorvee') === 'eigen') {
			$result = array_merge($result, $this->corveeTakenModel->getTakenVoorAgenda($van, $tot, false));
		}

		// Verjaardagen
		if (!$zijbalk && LoginModel::mag('P_VERJAARDAGEN', $auth) AND LidInstellingenModel::get('agenda', 'toonVerjaardagen') === 'ja') {
			//Verjaardagen. Omdat Lid-objectjes eigenlijk niet Agendeerbaar, maar meer iets als
			//PeriodiekAgendeerbaar zijn, maar we geen zin hebben om dat te implementeren,
			//doen we hier even een vieze hack waardoor het wel soort van werkt.
			$GLOBALS['agenda_jaar'] = date('Y', $van);
			$GLOBALS['agenda_maand'] = date('m', ($van + $tot) / 2);

			$result = array_merge($result, VerjaardagenModel::getTussen($van, $tot, 0)->fetchAll());
		}

		// Sorteren
		usort($result, array(AgendaModel::class, 'vergelijkAgendeerbaars'));

		return $result;
	}

	/**
	 * Vergelijkt twee Agendeerbaars op beginMoment t.b.v. sorteren.
	 * @param Agendeerbaar $foo
	 * @param Agendeerbaar $bar
	 * @return int
	 */
	public static function vergelijkAgendeerbaars(Agendeerbaar $foo, Agendeerbaar $bar) {
		$a = $foo->getBeginMoment();
		$b = $bar->getBeginMoment();
		if ($a > $b) {
			return 1;
		} elseif ($a < $b) {
			return -1;
		} else {
			return 0;
		}
	}

	public function filterVerborgen(array $items) {
		// Items verbergen
		$itemsByUUID = array();
		foreach ($items as $index => $item) {
			$itemsByUUID[$item->getUUID()] = $item;
			unset($items[$index]);
		}
		$count = count($itemsByUUID);
		if ($count > 0) {
			$params = array_keys($itemsByUUID);
			array_unshift($params, LoginModel::getUid());
			$verborgen = $this->agendaVerbergenModel->find('uid = ? AND refuuid IN (' . implode(', ', array_fill(0, $count, '?')) . ')', $params);
			foreach ($verborgen as $verbergen) {
				unset($itemsByUUID[$verbergen->refuuid]);
			}
		}
		return $itemsByUUID;
	}

	/**
	 * @param $itemId
	 * @return AgendaItem|false
	 */
	public function getAgendaItem($itemId) {
		return $this->retrieveByPrimaryKey(array($itemId));
	}

	public function getICalendarItems() {
		return $this->filterVerborgen($this->getAllAgendeerbaar(strtotime(InstellingenModel::get('agenda', 'ical_from')), strtotime(InstellingenModel::get('agenda', 'ical_to')), true));
	}

	public function getItemsByDay($jaar, $maand, $dag) {
		$time = mktime(0, 0, 0, $maand, $dag, $jaar);
		return $this->getAllAgendeerbaar($time, $time);
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
			$begin = $item->getBeginMoment();
			$eind = $item->getEindMoment();
			// Plaats in dag(en)
			for ($cur = $begin; $cur <= $eind; $cur += 86400) {
				$week = getWeekNumber($cur);
				$dag = date('d', $cur);
				if (isset($agenda[$week][$dag])) {
					$agenda[$week][$dag]['items'][] = $item;
				} else {
					continue; // multi-dag event gaat over maandgrens heen
				}
			}
		}
		return $agenda;
	}

	/**
	 * Zoek in de activiteiten (titel en beschrijving) van vandaag
	 * naar het woord $woord, geef de eerste terug.
	 * @param $woord string
	 * @return mixed|null
	 */
	public function zoekWoordAgenda($woord) {
		foreach ($this->getItemsByDay(date('Y'), date('m'), date('d')) as $item) {
			if (stristr($item->getTitel(), $woord) !== false OR stristr($item->getBeschrijving(), $woord) !== false) {
				return $item;
			}
		}
		return null;
	}

	public function nieuw($datum) {
		$item = new AgendaItem();
		if (!preg_match('/^[0-9]{4}\-[0-9]{1,2}-[0-9]{1,2}$/', $datum)) {
			$datum = strtotime('Y-m-d');
		}
		$item->begin_moment = getDateTime(strtotime($datum) + 72000);
		$item->eind_moment = getDateTime(strtotime($datum) + 79200);
		if (LoginModel::mag('P_AGENDA_MOD')) {
			$item->rechten_bekijken = InstellingenModel::get('agenda', 'standaard_rechten');
		} else {
			$item->rechten_bekijken = 'verticale:' . LoginModel::getProfiel()->verticale;
		}
		return $item;
	}

}
