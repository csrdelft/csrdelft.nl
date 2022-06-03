<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\view\maalcie\forms\BoekjaarSluitenForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaalCieBoekjaarController extends AbstractController
{
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;

	public function __construct(MaaltijdenRepository $maaltijdenRepository)
	{
		$this->maaltijdenRepository = $maaltijdenRepository;
	}

	/**
	 * @return Response
	 * @Route("/maaltijden/boekjaar", methods={"GET"})
	 * @Auth(P_MAAL_SALDI)
	 */
	public function beheer()
	{
		return $this->render('maaltijden/boekjaar_sluiten.html.twig');
	}

	/**
	 * @return BoekjaarSluitenForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/boekjaar/sluitboekjaar", methods={"POST"})
	 * @Auth(P_MAAL_SALDI)
	 */
	public function sluitboekjaar()
	{
		$form = new BoekjaarSluitenForm(date('Y-m-d', strtotime('-1 year')), date('Y-m-d')); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = $this->maaltijdenRepository->archiveerOudeMaaltijden(strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (count($errors_aantal[0]) === 0) {
				setMelding('Boekjaar succesvol gesloten: ' . $errors_aantal[1] . ' maaltijden naar het archief verplaatst.', 1);
			}
			return $this->render('maaltijden/boekjaar_sluiten.html.twig');
		} else {
			return $form;
		}
	}

}
