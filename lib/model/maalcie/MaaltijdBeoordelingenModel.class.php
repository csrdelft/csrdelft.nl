<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\entity\maalcie\MaaltijdBeoordeling;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

/**
 * MaaltijdBeoordelingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MaaltijdBeoordelingenModel extends PersistenceModel {

	const ORM = MaaltijdBeoordeling::class;

	public function nieuw(Maaltijd $maaltijd) {
		$b = new MaaltijdBeoordeling();
		$b->maaltijd_id = $maaltijd->maaltijd_id;
		$b->uid = LoginModel::getUid();
		$b->kwantiteit = null;
		$b->kwaliteit = null;
		$this->create($b);
		return $b;
	}

	public function getBeoordelingSamenvatting(Maaltijd $maaltijd) {
	    // Haal beoordelingen voor deze maaltijd op
        $beoordelingen = $this->find('maaltijd_id = ?', array($maaltijd->maaltijd_id));

        // Bepaal gemiddelde en gemiddelde afwijking
        $kwantiteit = 0;
        $kwantiteit_afwijking = 0;
        $kwantiteit_aantal = 0;
        $kwaliteit_afwijking = 0;
        $kwaliteit = 0;
        $kwaliteit_aantal = 0;
        foreach ($beoordelingen as $b) {
            // Haal gemiddelde beoordeling van lid op
            $userAverage = Database::instance()->sqlSelect(array('AVG(kwantiteit)', 'AVG(kwaliteit)'), $this->getTableName(), 'uid = ?', array($b->uid));
            $userAverage->execute();
            $avg = $userAverage->fetchAll();

            // Alleen als waarde is ingevuld
            if (!is_null($b->kwantiteit)) {
                $kwantiteit += $b->kwantiteit;
                // Bepaal afwijking en tel op
                $kwantiteit_afwijking += $b->kwantiteit - $avg[0][0];
                $kwantiteit_aantal++;
            }
            if (!is_null($b->kwaliteit)) {
                $kwaliteit += $b->kwaliteit;
                // Bepaal afwijking en tel op
                $kwaliteit_afwijking += $b->kwaliteit - $avg[0][1];
                $kwaliteit_aantal++;
            }
        }

        // Geef resultaat terug in object, null als er geen beoordelingen zijn
        $object = new \stdClass();
        $object->kwantiteit = $kwantiteit_aantal === 0 ? null : round($kwantiteit / $kwantiteit_aantal, 3);
        $object->kwantiteit_afwijking = $kwantiteit_aantal === 0 ? null : round($kwantiteit_afwijking / $kwantiteit_aantal, 3);
        $object->kwantiteit_aantal = $kwantiteit_aantal;

        $object->kwaliteit = $kwaliteit_aantal === 0 ? null : round($kwaliteit / $kwaliteit_aantal, 3);
        $object->kwaliteit_afwijking = $kwaliteit_aantal === 0 ? null : round($kwaliteit_afwijking / $kwaliteit_aantal, 3);
        $object->kwaliteit_aantal = $kwaliteit_aantal;

        return $object;
    }

}
