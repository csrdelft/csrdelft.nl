<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerAbonnementenController {
	/**
	 * @var MaaltijdAbonnementenRepository
	 */
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
	 * @Route("/maaltijden/abonnementen/beheer", methods={"GET"})
	 * @Route("/maaltijden/abonnementen/beheer/waarschuwingen", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function waarschuwingen() {
		$matrix_repetities = $this->maaltijdAbonnementenRepository->getAbonnementenWaarschuwingenMatrix();

		return view('maaltijden.abonnement.beheer_abonnementen', [
			'toon' => 'waarschuwing',
			'aborepetities' => $this->maaltijdRepetitiesRepository->findBy(['abonneerbaar' => 'true']),
			'repetities' => $matrix_repetities[1],
			'matrix' => $matrix_repetities[0],
		]);
	}

	/**
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/ingeschakeld", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function ingeschakeld() {
		$matrix_repetities = $this->maaltijdAbonnementenRepository->getAbonnementenMatrix();

		return view('maaltijden.abonnement.beheer_abonnementen', [
			'toon' => 'in',
			'aborepetities' => $this->maaltijdRepetitiesRepository->findBy(['abonneerbaar' => 'true']),
			'repetities' => $matrix_repetities[1],
			'matrix' => $matrix_repetities[0],
		]);
	}

	/**
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/abonneerbaar", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function abonneerbaar() {
		$matrix_repetities = $this->maaltijdAbonnementenRepository->getAbonnementenAbonneerbaarMatrix();

		return view('maaltijden.abonnement.beheer_abonnementen', [
			'toon' => 'waarschuwing',
			'aborepetities' => $this->maaltijdRepetitiesRepository->findBy(['abonneerbaar' => 'true']),
			'repetities' => $matrix_repetities[1],
			'matrix' => $matrix_repetities[0],
		]);
	}

	/**
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/novieten", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function novieten() {
		$mrid = filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
		$repetitie = $this->maaltijdRepetitiesRepository->find($mrid);
		$aantal = $this->maaltijdAbonnementenRepository->inschakelenAbonnementVoorNovieten($repetitie);
		$matrix = $this->maaltijdAbonnementenRepository->getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		setMelding(
			$aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' aangemaakt voor ' .
			$novieten . ' noviet' . ($novieten !== 1 ? 'en' : '') . '.', 1);
		return view('maaltijden.abonnement.beheer_abonnementen_lijst', ['matrix' => $matrix]);
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/inschakelen/{mlt_repetitie_id}/{uid}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function inschakelen(MaaltijdRepetitie $repetitie, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$abo = new MaaltijdAbonnement();
		$abo->maaltijd_repetitie = $repetitie;
		$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
		$abo->uid = $uid;
		$aantal = $this->maaltijdAbonnementenRepository->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return view('maaltijden.abonnement.beheer_abonnement', ['abonnement' => $abo]);
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid
	 * @return TemplateView
	 * @throws Throwable
	 * @Route("/maaltijden/abonnementen/beheer/uitschakelen/{mlt_repetitie_id}/{uid}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function uitschakelen(MaaltijdRepetitie $repetitie, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$abo_aantal = $this->maaltijdAbonnementenRepository->uitschakelenAbonnement($repetitie, $uid);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return view('maaltijden.abonnement.beheer_abonnement', ['abonnement' => $abo_aantal[0]]);
	}

}
