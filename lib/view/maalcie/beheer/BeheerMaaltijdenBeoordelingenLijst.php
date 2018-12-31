<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\entity\maalcie\CorveeFunctie;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\maalcie\MaaltijdBeoordelingenModel;
use CsrDelft\model\ProfielModel;
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
		$data['aantal_beoordelingen'] = $stat->kwantiteitAantal . ", " . $stat->kwaliteitAantal;
		$data['kwantiteit'] = $this->getalWeergave($stat->kwantiteit, '-', 3);
		$data['kwaliteit'] = $this->getalWeergave($stat->kwaliteit, '-', 3);
		$data['kwantiteit_afwijking'] = $this->getalWeergave($stat->kwantiteitAfwijking, '-', 3, true);
		$data['kwaliteit_afwijking'] = $this->getalWeergave($stat->kwaliteitAfwijking, '-', 3, true);

		// Haal koks op
		$kokTaken = $maaltijd->getCorveeTaken(CorveeFunctie::KWALIKOK_FUNCTIE_ID);
		$data['koks'] = "";
		for ($i = 0; $i < count($kokTaken); $i++) {
			$data['koks'] .= ProfielModel::getLink($kokTaken[$i]->uid);
			if ($i < count($kokTaken) - 1) $data['koks'] .= '<br>';
		}

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
