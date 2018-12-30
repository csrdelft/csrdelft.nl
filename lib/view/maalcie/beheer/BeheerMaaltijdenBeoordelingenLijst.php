<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\maalcie\MaaltijdBeoordelingenModel;
use CsrDelft\view\datatable\DataTableResponse;

class BeheerMaaltijdenBeoordelingenLijst extends DataTableResponse {
    /**
	 * @param Maaltijd $maaltijd
	 *
	 * @return string
	 */
	public function getJson($maaltijd) {
		$data = $maaltijd->jsonSerialize();

		// Haal beoordelingsamenvatting op
        $stat = MaaltijdBeoordelingenModel::instance()->getBeoordelingSamenvatting($maaltijd);

        $data['aantal_beoordelingen'] = $stat->kwantiteit_aantal . ", " . $stat->kwaliteit_aantal;
        $data['kwantiteit'] = $this->getalWeergave($stat->kwantiteit, 'n.a.', 3);
        $data['kwaliteit'] = $this->getalWeergave($stat->kwaliteit, 'n.a.', 3);
        $data['kwantiteit_afwijking'] = $this->getalWeergave($stat->kwantiteit_afwijking, 'n.a.', 3, true);
        $data['kwaliteit_afwijking'] = $this->getalWeergave($stat->kwaliteit_afwijking, 'n.a.', 3, true);

		return parent::getJson($data);
	}

	private function getalWeergave($number, $placeholder, $precision, $showPlus = false) {
	    if ($number === null) {
	        return $placeholder;
        } else {
	        $plus = $showPlus && $number > 0 ? '+' : '';
            return $plus . round($number, $precision);
        }
    }
}
