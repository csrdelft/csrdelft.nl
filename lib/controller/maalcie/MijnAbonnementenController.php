<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
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
	/** @var MaaltijdAbonnementenRepository  */
	private $maaltijdAbonnementenRepository;
	/**
	 * @var MaaltijdAbonnementenService
	 */
	private $maaltijdAbonnementenService;

	public function __construct(
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
		MaaltijdAbonnementenService $maaltijdAbonnementenService
	) {
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->maaltijdAbonnementenService = $maaltijdAbonnementenService;
	}

	/**
	 * @return Response
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen", methods={"GET"})
	 * @Auth(P_MAAL_IK)
	 */
	public function mijn()
	{
		$abonnementen = $this->maaltijdAbonnementenService->getAbonnementenVoorLid(
			$this->getUid(),
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
	 * @Route("/maaltijden/abonnementen/inschakelen/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function inschakelen(MaaltijdRepetitie $repetitie)
	{
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
		$abo->maaltijd_repetitie = $repetitie;
		$abo->uid = $this->getUid();
		$aantal = $this->maaltijdAbonnementenService->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding =
				'Automatisch aangemeld voor ' .
				$aantal .
				' maaltijd' .
				($aantal === 1 ? '' : 'en');
			MeldingUtil::setMelding($melding, 2);
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
	 * @Route("/maaltijden/abonnementen/uitschakelen/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function uitschakelen(MaaltijdRepetitie $repetitie)
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
			MeldingUtil::setMelding($melding, 2);
		}
		$abo = $abo_aantal[0];
		return $this->render('maaltijden/abonnement/mijn_abonnement.html.twig', [
			'uid' => $abo->uid,
			'mrid' => $abo->mlt_repetitie_id,
		]);
	}
}
