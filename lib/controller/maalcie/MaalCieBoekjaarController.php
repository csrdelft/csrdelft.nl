<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\maalcie\forms\BoekjaarSluitenForm;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaalCieBoekjaarController {
	/** @var MaaltijdenModel  */
	private $maaltijdenModel;

	public function __construct(MaaltijdenModel $maaltijdenModel) {
		$this->maaltijdenModel = $maaltijdenModel;
	}

	public function beheer() {
		return view('maaltijden.boekjaar_sluiten');
	}

	public function sluitboekjaar() {
		$form = new BoekjaarSluitenForm(date('Y-m-d', strtotime('-1 year')), date('Y-m-d')); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = $this->maaltijdenModel->archiveerOudeMaaltijden(strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (count($errors_aantal[0]) === 0) {
				setMelding('Boekjaar succesvol gesloten: ' . $errors_aantal[1] . ' maaltijden naar het archief verplaatst.', 1);
			}
			return view('maaltijden.boekjaar_sluiten');
		} else {
			return $form;
		}
	}

}
