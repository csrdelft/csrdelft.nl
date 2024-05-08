<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\service\maalcie\MaaltijdAbonnementenService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * MijnAbonnementenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnAbonnementenController extends AbstractController
{
	/**
	 * @var MaaltijdAbonnementenService
	 */
	private $maaltijdAbonnementenService;

	public function __construct(
		MaaltijdAbonnementenService $maaltijdAbonnementenService
	) {
		$this->maaltijdAbonnementenService = $maaltijdAbonnementenService;
	}

	/**
  * @return Response
  * @throws Throwable
  * @Auth(P_MAAL_IK)
  */
 #[Route(path: '/maaltijden/abonnementen', methods: ['GET'])]
 public function mijn(): Response
	{
		$abonnementen = $this->maaltijdAbonnementenService->getAbonnementenVoorLid(
			$this->getProfiel(),
			true,
			true
		);
		return $this->render('maaltijden/abonnement/mijn_abonnementen.html.twig', [
			'titel' => 'Mijn abonnementen',
			'abonnementen' => $abonnementen,
		]);
	}

	/**
  * @param MaaltijdRepetitie $repetitie
  * @return Response
  * @throws Throwable
  * @Auth(P_MAAL_IK)
  */
 #[Route(path: '/maaltijden/abonnementen/inschakelen/{mlt_repetitie_id}', methods: ['POST'])]
 public function inschakelen(MaaltijdRepetitie $repetitie): Response
	{
		$abo = new MaaltijdAbonnement();
		$abo->setMaaltijdRepetitie($repetitie);
		$abo->setProfiel($this->getProfiel());
		$aantal = $this->maaltijdAbonnementenService->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding =
				'Automatisch aangemeld voor ' .
				$aantal .
				' maaltijd' .
				($aantal === 1 ? '' : 'en');
			$this->addFlash(FlashType::WARNING, $melding);
		}
		return $this->render('maaltijden/abonnement/mijn_abonnement.html.twig', [
			'uid' => $abo->uid,
			'mrid' => $abo->mlt_repetitie_id,
		]);
	}

	/**
  * @param MaaltijdRepetitie $repetitie
  * @return Response
  * @throws Throwable
  * @Auth(P_MAAL_IK)
  */
 #[Route(path: '/maaltijden/abonnementen/uitschakelen/{mlt_repetitie_id}', methods: ['POST'])]
 public function uitschakelen(MaaltijdRepetitie $repetitie): Response
	{
		$abo_aantal = $this->maaltijdAbonnementenService->uitschakelenAbonnement(
			$repetitie,
			$this->getUid()
		);
		if ($abo_aantal[1] > 0) {
			$melding =
				'Automatisch afgemeld voor ' .
				$abo_aantal[1] .
				' maaltijd' .
				($abo_aantal[1] === 1 ? '' : 'en');
			$this->addFlash(FlashType::WARNING, $melding);
		}
		$abo = $abo_aantal[0];
		return $this->render('maaltijden/abonnement/mijn_abonnement.html.twig', [
			'uid' => $abo->uid,
			'mrid' => $abo->mlt_repetitie_id,
		]);
	}
}
