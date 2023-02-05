<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\maalcie\MaaltijdAbonnementenService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerAbonnementenController extends AbstractController
{
	/**
	 * @var MaaltijdAbonnementenRepository
	 */
	private $maaltijdAbonnementenRepository;
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;
	/**
	 * @var MaaltijdAbonnementenService
	 */
	private $maaltijdAbonnementenService;

	public function __construct(
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
		MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository,
		MaaltijdAbonnementenService $maaltijdAbonnementenService
	) {
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
		$this->maaltijdAbonnementenService = $maaltijdAbonnementenService;
	}

	/**
	 * @return Response
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer", methods={"GET"})
	 * @Route("/maaltijden/abonnementen/beheer/waarschuwingen", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function waarschuwingen()
	{
		$matrix_repetities = $this->maaltijdAbonnementenService->getAbonnementenWaarschuwingenMatrix();

		return $this->render(
			'maaltijden/abonnement/beheer_abonnementen.html.twig',
			[
				'toon' => 'waarschuwing',
				'aborepetities' => $this->maaltijdRepetitiesRepository->findBy([
					'abonneerbaar' => 'true',
				]),
				'repetities' => $matrix_repetities[1],
				'matrix' => $matrix_repetities[0],
			]
		);
	}

	/**
	 * @return Response
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/ingeschakeld", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function ingeschakeld()
	{
		$matrix_repetities = $this->maaltijdAbonnementenService->getAbonnementenMatrix();

		return $this->render(
			'maaltijden/abonnement/beheer_abonnementen.html.twig',
			[
				'toon' => 'in',
				'aborepetities' => $this->maaltijdRepetitiesRepository->findBy([
					'abonneerbaar' => 'true',
				]),
				'repetities' => $matrix_repetities[1],
				'matrix' => $matrix_repetities[0],
			]
		);
	}

	/**
	 * @return Response
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/abonneerbaar", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function abonneerbaar()
	{
		$matrix_repetities = $this->maaltijdAbonnementenService->getAbonnementenAbonneerbaarMatrix();

		return $this->render(
			'maaltijden/abonnement/beheer_abonnementen.html.twig',
			[
				'toon' => 'abo',
				'aborepetities' => $this->maaltijdRepetitiesRepository->findBy([
					'abonneerbaar' => 'true',
				]),
				'repetities' => $matrix_repetities[1],
				'matrix' => $matrix_repetities[0],
			]
		);
	}

	/**
	 * @return Response
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/novieten", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function novieten()
	{
		$mrid = filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
		$repetitie = $this->maaltijdRepetitiesRepository->find($mrid);
		$aantal = $this->maaltijdAbonnementenRepository->inschakelenAbonnementVoorNovieten(
			$repetitie
		);
		$matrix = $this->maaltijdAbonnementenService->getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		MeldingUtil::setMelding(
			$aantal .
				' abonnement' .
				($aantal !== 1 ? 'en' : '') .
				' aangemaakt voor ' .
				$novieten .
				' noviet' .
				($novieten !== 1 ? 'en' : '') .
				'.',
			1
		);
		return $this->render(
			'maaltijden/abonnement/beheer_abonnementen_lijst.html.twig',
			['matrix' => $matrix]
		);
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid
	 * @return Response
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/inschakelen/{mlt_repetitie_id}/{uid}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function inschakelen(MaaltijdRepetitie $repetitie, $uid)
	{
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(
				sprintf('Lid met uid "%s" bestaat niet.', $uid)
			);
		}
		$abo = new MaaltijdAbonnement();
		$abo->maaltijd_repetitie = $repetitie;
		$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
		$abo->uid = $uid;
		$aantal = $this->maaltijdAbonnementenRepository->inschakelenAbonnement(
			$abo
		);
		if ($aantal > 0) {
			$melding =
				'Automatisch aangemeld voor ' .
				$aantal .
				' maaltijd' .
				($aantal === 1 ? '' : 'en');
			MeldingUtil::setMelding($melding, 2);
		}
		return $this->render('maaltijden/abonnement/beheer_abonnement.html.twig', [
			'abonnement' => $abo,
		]);
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid
	 * @return Response
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/uitschakelen/{mlt_repetitie_id}/{uid}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function uitschakelen(MaaltijdRepetitie $repetitie, $uid)
	{
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(
				sprintf('Lid met uid "%s" bestaat niet.', $uid)
			);
		}
		$abo_aantal = $this->maaltijdAbonnementenRepository->uitschakelenAbonnement(
			$repetitie,
			$uid
		);
		if ($abo_aantal[1] > 0) {
			$melding =
				'Automatisch afgemeld voor ' .
				$abo_aantal[1] .
				' maaltijd' .
				($abo_aantal[1] === 1 ? '' : 'en');
			MeldingUtil::setMelding($melding, 2);
		}
		return $this->render('maaltijden/abonnement/beheer_abonnement.html.twig', [
			'abonnement' => $abo_aantal[0],
		]);
	}
}
