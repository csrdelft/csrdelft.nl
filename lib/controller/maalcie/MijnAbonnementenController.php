<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * MijnAbonnementenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnAbonnementenController {
	/** @var MaaltijdAbonnementenRepository  */
	private $maaltijdAbonnementenRepository;
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;

	public function __construct(MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository, MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository) {
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
	}

	/**
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen", methods={"GET"})
	 * @Auth(P_MAAL_IK)
	 */
	public function mijn() {
		$abonnementen = $this->maaltijdAbonnementenRepository->getAbonnementenVoorLid(LoginService::getUid(), true, true);
		return view('maaltijden.abonnement.mijn_abonnementen', ['titel' => 'Mijn abonnementen', 'abonnementen' => $abonnementen]);
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/inschakelen/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function inschakelen(MaaltijdRepetitie $repetitie) {
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
		$abo->maaltijd_repetitie = $repetitie;
		$abo->uid = LoginService::getUid();
		$aantal = $this->maaltijdAbonnementenRepository->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return view('maaltijden.abonnement.mijn_abonnement', ['uid' => $abo->uid, 'mrid' => $abo->mlt_repetitie_id]);
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/uitschakelen/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function uitschakelen(MaaltijdRepetitie $repetitie) {
		$abo_aantal = $this->maaltijdAbonnementenRepository->uitschakelenAbonnement($repetitie, LoginService::getUid());
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		$abo = $abo_aantal[0];
		return view('maaltijden.abonnement.mijn_abonnement', ['uid' => $abo->uid, 'mrid' => $abo->mlt_repetitie_id]);
	}

}
