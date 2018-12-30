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
        $beoordelingSamenvatting = MaaltijdBeoordelingenModel::instance()->getBeoordelingSamenvatting($maaltijd);
        foreach ($beoordelingSamenvatting as $key => $value) {
            $data[$key] = $value;
        }

		return parent::getJson($data);
	}
}
