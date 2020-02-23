<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\repository\ProfielRepository;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerAbonnementenController {
	/**
	 * @var MaaltijdAbonnementenModel
	 */
	private $maaltijdAbonnementenModel;
	/**
	 * @var MaaltijdRepetitiesModel
	 */
	private $maaltijdRepetitiesModel;

	public function __construct(MaaltijdAbonnementenModel $maaltijdAbonnementenModel, MaaltijdRepetitiesModel $maaltijdRepetitiesModel) {
		$this->maaltijdAbonnementenModel = $maaltijdAbonnementenModel;
		$this->maaltijdRepetitiesModel = $maaltijdRepetitiesModel;
	}

	public function waarschuwingen() {
		$matrix_repetities = $this->maaltijdAbonnementenModel->getAbonnementenWaarschuwingenMatrix();

		return view('maaltijden.abonnement.beheer_abonnementen', [
			'toon' => 'waarschuwing',
			'aborepetities' => $this->maaltijdRepetitiesModel->find('abonneerbaar = true'),
			'repetities' => $matrix_repetities[1],
			'matrix' => $matrix_repetities[0],
		]);
	}

	public function ingeschakeld() {
		$matrix_repetities = $this->maaltijdAbonnementenModel->getAbonnementenMatrix();

		return view('maaltijden.abonnement.beheer_abonnementen', [
			'toon' => 'in',
			'aborepetities' => $this->maaltijdRepetitiesModel->find('abonneerbaar = true'),
			'repetities' => $matrix_repetities[1],
			'matrix' => $matrix_repetities[0],
		]);
	}

	public function abonneerbaar() {
		$matrix_repetities = $this->maaltijdAbonnementenModel->getAbonnementenAbonneerbaarMatrix();

		return view('maaltijden.abonnement.beheer_abonnementen', [
			'toon' => 'waarschuwing',
			'aborepetities' => $this->maaltijdRepetitiesModel->find('abonneerbaar = true'),
			'repetities' => $matrix_repetities[1],
			'matrix' => $matrix_repetities[0],
		]);
	}

	public function novieten() {
		$mrid = filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
		$aantal = $this->maaltijdAbonnementenModel->inschakelenAbonnementVoorNovieten((int)$mrid);
		$matrix = $this->maaltijdAbonnementenModel->getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		setMelding(
			$aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' aangemaakt voor ' .
			$novieten . ' noviet' . ($novieten !== 1 ? 'en' : '') . '.', 1);
		return view('maaltijden.abonnement.beheer_abonnementen_lijst', ['matrix' => $matrix]);
	}

	public function inschakelen($mrid, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat nie.', $uid));
		}
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $mrid;
		$abo->uid = $uid;
		$aantal = $this->maaltijdAbonnementenModel->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return view('maaltijden.abonnement.beheer_abonnement', ['abonnement' => $abo]);
	}

	public function uitschakelen($mrid, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$abo_aantal = $this->maaltijdAbonnementenModel->uitschakelenAbonnement((int)$mrid, $uid);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return view('maaltijden.abonnement.beheer_abonnement', ['abonnement' => $abo_aantal[0]]);
	}

}
