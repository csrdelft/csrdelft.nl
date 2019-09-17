<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\beheer\MaalCieBoekjaarSluitenView;
use CsrDelft\view\maalcie\forms\BoekjaarSluitenForm;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaalCieBoekjaarController {
	public function beheer() {
		$view = new MaalCieBoekjaarSluitenView();
		return new CsrLayoutPage($view);
	}

	public function sluitboekjaar() {
		$form = new BoekjaarSluitenForm(date('Y-m-d', strtotime('-1 year')), date('Y-m-d')); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = MaaltijdenModel::instance()->archiveerOudeMaaltijden(strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (sizeof($errors_aantal[0]) === 0) {
				setMelding('Boekjaar succesvol gesloten: ' . $errors_aantal[1] . ' maaltijden naar het archief verplaatst.', 1);
			}
			return new MaalCieBoekjaarSluitenView();
		} else {
			return $form;
		}
	}

}
