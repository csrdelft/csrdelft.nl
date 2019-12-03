<?php

namespace CsrDelft\model;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;

/**
 * VerjaardagenModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class VerjaardagenModel {

	/**
	 * @return Profiel[][]
	 */
	public static function getJaar() {
		return array_map('static::get', range(1, 12));
	}

	/**
	 * @param $maand
	 *
	 * @return Profiel[]
	 */
	public static function get($maand) {
		$container = ContainerFacade::getContainer();
		return $container->get(ProfielRepository::class)->ormFind("status in ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND EXTRACT(MONTH FROM gebdatum) = ? ", array($maand), null, 'EXTRACT(DAY FROM gebdatum)');
	}

	/**
	 * @param int $aantal
	 *
	 * @return Profiel[]
	 */
	public static function getKomende($aantal = 10) {
		$container = ContainerFacade::getContainer();
		return $container->get(ProfielRepository::class)->ormFind("status IN ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND NOT gebdatum = '0000-00-00'", array(), null, "DATE_ADD(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) ASC", $aantal);
	}

	/**
	 * @param int $van
	 * @param int $tot
	 * @param int $limiet
	 *
	 * @return Profiel[]
	 */
	public static function getTussen($van, $tot, $limiet = null) {
		$vanjaar = date('Y', $van);
		$totjaar = date('Y', $tot);
		$van = date('Y-m-d', $van);
		$tot = date('Y-m-d', $tot);

		$container = ContainerFacade::getContainer();

		return $container->get(ProfielRepository::class)->ormFind("status IN ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND NOT gebdatum = '0000-00-00' AND (
            (CONCAT(?, SUBSTRING(gebdatum, 5)) >= ? AND CONCAT(?, SUBSTRING(gebdatum, 5)) < ?)
            OR
            (CONCAT(?, SUBSTRING(gebdatum, 5)) >= ? AND CONCAT(?, SUBSTRING(gebdatum, 5)) < ?)
        ) AND gebdatum <= ?", array($vanjaar, $van, $vanjaar, $tot, $totjaar, $van, $totjaar, $tot, $tot), null, "DATE_ADD(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				)", $limiet);
	}
}
