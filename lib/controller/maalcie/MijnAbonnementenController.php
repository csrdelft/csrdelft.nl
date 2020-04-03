<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;

/**
 * MijnAbonnementenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnAbonnementenController {
	/** @var MaaltijdAbonnementenRepository  */
	private $maaltijdAbonnementenRepository;

	public function __construct(MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository) {
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
	}

	public function mijn() {
		$abonnementen = $this->maaltijdAbonnementenRepository->getAbonnementenVoorLid(LoginModel::getUid(), true, true);
		return view('maaltijden.abonnement.mijn_abonnementen', ['titel' => 'Mijn abonnementen', 'abonnementen' => $abonnementen]);
	}

	public function inschakelen($mrid) {
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $mrid;
		$abo->uid = LoginModel::getUid();
		$aantal = $this->maaltijdAbonnementenRepository->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return view('maaltijden.abonnement.mijn_abonnement', ['uid' => $abo->uid, 'mrid' => $abo->mlt_repetitie_id]);
	}

	public function uitschakelen($mrid) {
		$abo_aantal = $this->maaltijdAbonnementenRepository->uitschakelenAbonnement((int)$mrid, LoginModel::getUid());
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		$abo = $abo_aantal[0];
		return view('maaltijden.abonnement.mijn_abonnement', ['uid' => $abo->uid, 'mrid' => $abo->mlt_repetitie_id]);
	}

}
