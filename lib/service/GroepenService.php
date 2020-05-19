<?php


namespace CsrDelft\service;


use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\AbstractGroepLid;
use CsrDelft\entity\groepen\GroepStatistiek;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\Orm\Persistence\Database;

class GroepenService {

	/**
	 * Bereken statistieken van de groepleden.
	 *
	 * @param AbstractGroep $groep
	 * @return GroepStatistiek
	 */
	public static function getStatistieken(AbstractGroep $groep) {
		/** @var AbstractGroepLid[] $leden */
		$leden = group_by_distinct('uid', $groep->getLeden());
		if (empty($leden)) {
			return new GroepStatistiek(0, [], [], [], []);
		}
		$uids = array_keys($leden);
		$count = count($uids);
		$sqlIn = implode(', ', array_fill(0, $count, '?'));
		$tijd = [];
		foreach ($leden as $groeplid) {
			$time = $groeplid->lid_sinds->getTimestamp();
			if (isset($tijd[$time])) {
				$tijd[$time] += 1;
			} else {
				$tijd[$time] = 1;
			}
		}
		ksort($tijd);
		$totaal = $count;
		if ($groep instanceof HeeftAanmeldLimiet) {
			if ($groep->getAanmeldLimiet() === null) {
				$totaal .= ' (geen limiet)';
			} else {
				$totaal .= ' van ' . $groep->getAanmeldLimiet();
			}
		}
		$db = ContainerFacade::getContainer()->get(Database::class);
		$profielTable = 'profielen';
		return new GroepStatistiek(
			$totaal,
			$db->sqlSelect(['naam', 'count(*)'], 'profielen LEFT JOIN verticalen ON profielen.verticale = verticalen.letter', 'uid IN (' . $sqlIn . ')', $uids, 'verticale', null)->fetchAll(),
			$db->sqlSelect(['geslacht', 'count(*)'], $profielTable, 'uid IN (' . $sqlIn . ')', $uids, 'geslacht', null)->fetchAll(),
			$db->sqlSelect(['lidjaar', 'count(*)'], $profielTable, 'uid IN (' . $sqlIn . ')', $uids, 'lidjaar', null)->fetchAll(),
			$tijd
		);
	}
}
