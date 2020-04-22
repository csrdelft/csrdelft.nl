<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\view\maalcie\forms\BoekjaarSluitenForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaalCieBoekjaarController {
	/** @var MaaltijdenRepository  */
	private $maaltijdenRepository;

	public function __construct(MaaltijdenRepository $maaltijdenRepository) {
		$this->maaltijdenRepository = $maaltijdenRepository;
	}

	public function beheer() {
		return view('maaltijden.boekjaar_sluiten');
	}

	/**
	 * @return BoekjaarSluitenForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function sluitboekjaar() {
		$form = new BoekjaarSluitenForm(date('Y-m-d', strtotime('-1 year')), date('Y-m-d')); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = $this->maaltijdenRepository->archiveerOudeMaaltijden(strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (count($errors_aantal[0]) === 0) {
				setMelding('Boekjaar succesvol gesloten: ' . $errors_aantal[1] . ' maaltijden naar het archief verplaatst.', 1);
			}
			return view('maaltijden.boekjaar_sluiten');
		} else {
			return $form;
		}
	}

}
