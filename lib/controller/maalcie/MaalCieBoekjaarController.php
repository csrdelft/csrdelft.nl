<?php

namespace CsrDelft\controller\maalcie;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\controller\AbstractController;
use CsrDelft\service\maalcie\MaaltijdArchiefService;
use CsrDelft\view\maalcie\forms\BoekjaarSluitenForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaalCieBoekjaarController extends AbstractController
{
	public function __construct(
		private readonly MaaltijdArchiefService $maaltijdArchiefService
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_MAAL_SALDI)
	 */
	#[Route(path: '/maaltijden/boekjaar', methods: ['GET'])]
	public function beheer()
	{
		return $this->render('maaltijden/boekjaar_sluiten.html.twig');
	}

	/**
	 * @return BoekjaarSluitenForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Auth(P_MAAL_SALDI)
	 */
	#[Route(path: '/maaltijden/boekjaar/sluitboekjaar', methods: ['POST'])]
	public function sluitboekjaar()
	{
		$form = new BoekjaarSluitenForm(
			date('Y-m-d', strtotime('-1 year')),
			date('Y-m-d')
		); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = $this->maaltijdArchiefService->archiveerOudeMaaltijden(
				strtotime((string) $values['begindatum']),
				strtotime((string) $values['einddatum'])
			);
			if (count($errors_aantal[0]) === 0) {
				$this->addFlash(
					FlashType::SUCCESS,
					'Boekjaar succesvol gesloten: ' .
						$errors_aantal[1] .
						' maaltijden naar het archief verplaatst.'
				);
			}
			return $this->render('maaltijden/boekjaar_sluiten.html.twig');
		} else {
			return $form;
		}
	}
}
