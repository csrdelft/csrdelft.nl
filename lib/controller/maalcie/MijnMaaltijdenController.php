<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnMaaltijdenController {
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var MaaltijdBeoordelingenRepository
	 */
	private $maaltijdBeoordelingenRepository;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(
		MaaltijdenRepository $maaltijdenRepository,
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdBeoordelingenRepository $maaltijdBeoordelingenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdBeoordelingenRepository = $maaltijdBeoordelingenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	/**
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function ketzer() {
		$maaltijden = $this->maaltijdenRepository->getKomendeMaaltijdenVoorLid(LoginService::getUid());
		$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid($maaltijden, LoginService::getUid());
		$timestamp = date_create_immutable(instelling('maaltijden', 'beoordeling_periode'));
		$recent = $this->maaltijdAanmeldingenRepository->getRecenteAanmeldingenVoorLid(LoginService::getUid(), $timestamp);
		$beoordelen = [];
		$kwantiteit_forms = [];
		$kwaliteit_forms = [];
		foreach ($maaltijden as $maaltijd) {
			$mid = $maaltijd->maaltijd_id;
			if (!array_key_exists($mid, $aanmeldingen)) {
				$aanmeldingen[$mid] = false;
			}
		}
		foreach ($recent as $aanmelding) {
			$maaltijd = $aanmelding->maaltijd;
			$mid = $aanmelding->maaltijd_id;
			$beoordelen[$mid] = $maaltijd;
			$beoordeling = $this->maaltijdBeoordelingenRepository->find(['maaltijd_id' => $mid, 'uid' => LoginService::getUid()]);
			if (!$beoordeling) {
				$beoordeling = $this->maaltijdBeoordelingenRepository->nieuw($maaltijd);
			}
			$kwantiteit_forms[$mid] = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
			$kwaliteit_forms[$mid] = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
		return view('maaltijden.maaltijd.mijn_maaltijden', [
			'standaardprijs' => intval(instelling('maaltijden', 'standaard_prijs')),
			'maaltijden' => $maaltijden,
			'aanmeldingen' => $aanmeldingen,
			'beoordelen' => $beoordelen,
			'kwantiteit' => $kwantiteit_forms,
			'kwaliteit' => $kwaliteit_forms,
		]);
	}

	public function lijst($mid) {
		$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid, true);
		if (!$maaltijd->magSluiten(LoginService::getUid()) AND !LoginService::mag(P_MAAL_MOD)) {
			throw new CsrToegangException();
		}
		$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorMaaltijd($maaltijd);
		for ($i = $maaltijd->getMarge(); $i > 0; $i--) { // ruimte voor marge eters
			$aanmeldingen[] = new MaaltijdAanmelding();
		}

		return view('maaltijden.maaltijd.maaltijd_lijst', [
			'titel' => $maaltijd->getTitel(),
			'aanmeldingen' => $aanmeldingen,
			'eterstotaal' => $maaltijd->getAantalAanmeldingen() + $maaltijd->getMarge(),
			'corveetaken' => $this->corveeTakenRepository->getTakenVoorMaaltijd($mid)->fetchAll(),
			'maaltijd' => $maaltijd,
			'prijs' => sprintf("%.2f", $maaltijd->getPrijsFloat()),
		]);
	}

	/**
	 * @param int $mid
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function sluit($mid) {
		$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);
		if (!$maaltijd->magSluiten(LoginService::getUid()) AND !LoginService::mag(P_MAAL_MOD)) {
			throw new CsrToegangException();
		}
		$this->maaltijdenRepository->sluitMaaltijd($maaltijd);
		echo '<h3 id="gesloten-melding" class="remove"></div>';
		exit;
	}

	/**
	 * @param Request $request
	 * @param int $mid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmelden(Request $request, $mid) {
		$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);
		$aanmelding = $this->maaltijdAanmeldingenRepository->aanmeldenVoorMaaltijd($maaltijd, LoginService::getUid(), LoginService::getUid());
		if ($request->getMethod() == 'POST') {
			return view('maaltijden.maaltijd.mijn_maaltijd_lijst', [
				'maaltijd' => $aanmelding->maaltijd,
				'aanmelding' => $aanmelding,
				'standaardprijs' => intval(instelling('maaltijden', 'standaard_prijs'))
			]);
		} else {
			return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
		}
	}

	/**
	 * @param Request $request
	 * @param int $mid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function afmelden(Request $request, $mid) {
		$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);
		$this->maaltijdAanmeldingenRepository->afmeldenDoorLid($maaltijd, LoginService::getUid());
		if ($request->getMethod() == 'POST') {
			return view('maaltijden.maaltijd.mijn_maaltijd_lijst', [
				'maaltijd' => $maaltijd,
				'standaardprijs' => intval(instelling('maaltijden', 'standaard_prijs'))
			]);
		} else {
			return view('maaltijden.bb', ['maaltijd' => $maaltijd]);
		}
	}

	/**
	 * @param int $mid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function gasten($mid) {
		$gasten = (int)filter_input(INPUT_POST, 'aantal_gasten', FILTER_SANITIZE_NUMBER_INT);
		$aanmelding = $this->maaltijdAanmeldingenRepository->saveGasten($mid, LoginService::getUid(), $gasten);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	/**
	 * @param int $mid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function opmerking($mid) {
		$opmerking = filter_input(INPUT_POST, 'gasten_eetwens', FILTER_SANITIZE_STRING);
		$aanmelding = $this->maaltijdAanmeldingenRepository->saveGastenEetwens($mid, LoginService::getUid(), $opmerking);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	/**
	 * @param int $mid
	 * @return JsonResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function beoordeling($mid) {
		$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);
		$beoordeling = $this->maaltijdBeoordelingenRepository->find(['maaltijd_id' => $mid, 'uid' => LoginService::getUid()]);
		if (!$beoordeling) {
			$beoordeling = $this->maaltijdBeoordelingenRepository->nieuw($maaltijd);
		}
		$form = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
		if (!$form->validate()) {
			$form = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
		if ($form->validate()) {
			$this->maaltijdBeoordelingenRepository->update($beoordeling);
		}
		return new JsonResponse(null);
	}

}
