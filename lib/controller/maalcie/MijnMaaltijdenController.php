<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnMaaltijdenController {
	/**
	 * @var MaaltijdenModel
	 */
	private $maaltijdenModel;
	/**
	 * @var CorveeTakenModel
	 */
	private $corveeTakenModel;
	/**
	 * @var MaaltijdBeoordelingenRepository
	 */
	private $maaltijdBeoordelingenRepository;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(
        MaaltijdenModel $maaltijdenModel,
        CorveeTakenModel $corveeTakenModel,
        MaaltijdBeoordelingenRepository $maaltijdBeoordelingenRepository,
        MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
		$this->maaltijdenModel = $maaltijdenModel;
		$this->corveeTakenModel = $corveeTakenModel;
		$this->maaltijdBeoordelingenRepository = $maaltijdBeoordelingenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	public function ketzer() {
		$maaltijden = $this->maaltijdenModel->getKomendeMaaltijdenVoorLid(LoginModel::getUid());
		$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid($maaltijden, LoginModel::getUid());
		$timestamp = strtotime(instelling('maaltijden', 'beoordeling_periode'));
		$recent = $this->maaltijdAanmeldingenRepository->getRecenteAanmeldingenVoorLid(LoginModel::getUid(), $timestamp);
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
			$beoordeling = $this->maaltijdBeoordelingenRepository->find(['maaltijd_id' => $mid, 'uid' => LoginModel::getUid()]);
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
		$maaltijd = $this->maaltijdenModel->getMaaltijd($mid, true);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND !LoginModel::mag(P_MAAL_MOD)) {
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
			'corveetaken' => $this->corveeTakenModel->getTakenVoorMaaltijd($mid)->fetchAll(),
			'maaltijd' => $maaltijd,
			'prijs' => sprintf("%.2f", $maaltijd->getPrijsFloat()),
		]);
	}

	public function sluit($mid) {
		$maaltijd = $this->maaltijdenModel->getMaaltijd($mid);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND !LoginModel::mag(P_MAAL_MOD)) {
			throw new CsrToegangException();
		}
		$this->maaltijdenModel->sluitMaaltijd($maaltijd);
		echo '<h3 id="gesloten-melding" class="remove"></div>';
		exit;
	}

	public function aanmelden(Request $request, $mid) {
		$maaltijd = $this->maaltijdenModel->getMaaltijd($mid);
		$aanmelding = $this->maaltijdAanmeldingenRepository->aanmeldenVoorMaaltijd($maaltijd, LoginModel::getUid(), LoginModel::getUid());
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

	public function afmelden(Request $request, $mid) {
		$maaltijd = $this->maaltijdenModel->getMaaltijd($mid);
		$this->maaltijdAanmeldingenRepository->afmeldenDoorLid($maaltijd, LoginModel::getUid());
		if ($request->getMethod() == 'POST') {
			return view('maaltijden.maaltijd.mijn_maaltijd_lijst', [
				'maaltijd' => $maaltijd,
				'standaardprijs' => intval(instelling('maaltijden', 'standaard_prijs'))
			]);
		} else {
			return view('maaltijden.bb', ['maaltijd' => $maaltijd]);
		}
	}

	public function gasten($mid) {
		$gasten = (int)filter_input(INPUT_POST, 'aantal_gasten', FILTER_SANITIZE_NUMBER_INT);
		$aanmelding = $this->maaltijdAanmeldingenRepository->saveGasten($mid, LoginModel::getUid(), $gasten);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	public function opmerking($mid) {
		$opmerking = filter_input(INPUT_POST, 'gasten_eetwens', FILTER_SANITIZE_STRING);
		$aanmelding = $this->maaltijdAanmeldingenRepository->saveGastenEetwens($mid, LoginModel::getUid(), $opmerking);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	public function beoordeling($mid) {
		$maaltijd = $this->maaltijdenModel->getMaaltijd($mid);
		$beoordeling = $this->maaltijdBeoordelingenRepository->find(['maaltijd_id' => $mid, 'uid' => LoginModel::getUid()]);
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
