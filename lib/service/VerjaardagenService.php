<?php

namespace CsrDelft\service;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class VerjaardagenService {
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(ProfielRepository $profielRepository) {
		$this->profielRepository = $profielRepository;
	}

	/**
	 * @return Profiel[][]
	 */
	public function getJaar() {
		return array_map([$this, 'get'], range(1, 12));
	}

	/**
	 * @param $maand
	 *
	 * @return Profiel[]
	 */
	public function get($maand) {
		return $this->profielRepository->ormFind("status in ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND EXTRACT(MONTH FROM gebdatum) = ? ", [$maand], null, 'EXTRACT(DAY FROM gebdatum)');
	}

	/**
	 * @param int $aantal
	 *
	 * @return Profiel[]
	 */
	public function getKomende($aantal = 10) {
		return $this->profielRepository->ormFind("status IN ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND NOT gebdatum = '0000-00-00'", [], null, "DATE_ADD(
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
	public function getTussen($van, $tot, $limiet = null) {
		$vanjaar = date('Y', $van);
		$totjaar = date('Y', $tot);
		$van = date('Y-m-d', $van);
		$tot = date('Y-m-d', $tot);

		return $this->profielRepository->ormFind("status IN ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND NOT gebdatum = '0000-00-00' AND (
            (CONCAT(?, SUBSTRING(gebdatum, 5)) >= ? AND CONCAT(?, SUBSTRING(gebdatum, 5)) < ?)
            OR
            (CONCAT(?, SUBSTRING(gebdatum, 5)) >= ? AND CONCAT(?, SUBSTRING(gebdatum, 5)) < ?)
        ) AND gebdatum <= ?", [$vanjaar, $van, $vanjaar, $tot, $totjaar, $van, $totjaar, $tot, $tot], null, "DATE_ADD(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				)", $limiet);
	}
}
