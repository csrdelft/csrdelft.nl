<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdBeoordelingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use CsrDelft\view\maalcie\persoonlijk\MijnMaaltijdenView;
use CsrDelft\view\maalcie\persoonlijk\MijnMaaltijdView;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnMaaltijdenController {
	private $model;

	public function __construct() {
		$this->model = MaaltijdenModel::instance();
	}

	public function ketzer() {
		$maaltijden = $this->model->getKomendeMaaltijdenVoorLid(LoginModel::getUid());
		$aanmeldingen = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorLid($maaltijden, LoginModel::getUid());
		$timestamp = strtotime(instelling('maaltijden', 'beoordeling_periode'));
		$recent = MaaltijdAanmeldingenModel::instance()->getRecenteAanmeldingenVoorLid(LoginModel::getUid(), $timestamp);
		$view = new MijnMaaltijdenView($maaltijden, $aanmeldingen, $recent);
		return view('default', ['content' => $view]);
	}

	public function lijst($mid) {
		$maaltijd = $this->model->getMaaltijd($mid, true);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND !LoginModel::mag(P_MAAL_MOD)) {
			throw new CsrToegangException();
		}
		$aanmeldingen = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorMaaltijd($maaltijd);
		for ($i = $maaltijd->getMarge(); $i > 0; $i--) { // ruimte voor marge eters
			$aanmeldingen[] = new MaaltijdAanmelding();
		}

		return view('maaltijden.maaltijd.maaltijd_lijst', [
			'titel' => $maaltijd->getTitel(),
			'aanmeldingen' => $aanmeldingen,
			'eterstotaal' => $maaltijd->getAantalAanmeldingen() + $maaltijd->getMarge(),
			'corveetaken' => CorveeTakenModel::instance()->getTakenVoorMaaltijd($mid)->fetchAll(),
			'maaltijd' => $maaltijd,
			'prijs' => sprintf("%.2f", $maaltijd->getPrijsFloat()),
		]);
	}

	public function sluit($mid) {
		$maaltijd = $this->model->getMaaltijd($mid);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND !LoginModel::mag(P_MAAL_MOD)) {
			throw new CsrToegangException();
		}
		$this->model->sluitMaaltijd($maaltijd);
		echo '<h3 id="gesloten-melding" class="remove"></div>';
		exit;
	}

	public function aanmelden(Request $request, $mid) {
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		$aanmelding = MaaltijdAanmeldingenModel::instance()->aanmeldenVoorMaaltijd($maaltijd, LoginModel::getUid(), LoginModel::getUid());
		if ($request->getMethod() == 'POST') {
			return new MijnMaaltijdView($aanmelding->maaltijd, $aanmelding);
		} else {
			return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
		}
	}

	public function afmelden(Request $request, $mid) {
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		MaaltijdAanmeldingenModel::instance()->afmeldenDoorLid($maaltijd, LoginModel::getUid());
		if ($request->getMethod() == 'POST') {
			return new MijnMaaltijdView($maaltijd);
		} else {
			return view('maaltijden.bb', ['maaltijd' => $maaltijd]);
		}
	}

	public function gasten($mid) {
		$gasten = (int)filter_input(INPUT_POST, 'aantal_gasten', FILTER_SANITIZE_NUMBER_INT);
		$aanmelding = MaaltijdAanmeldingenModel::instance()->saveGasten($mid, LoginModel::getUid(), $gasten);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	public function opmerking($mid) {
		$opmerking = filter_input(INPUT_POST, 'gasten_eetwens', FILTER_SANITIZE_STRING);
		$aanmelding = MaaltijdAanmeldingenModel::instance()->saveGastenEetwens($mid, LoginModel::getUid(), $opmerking);
		return view('maaltijden.bb', ['maaltijd' => $aanmelding->maaltijd, 'aanmelding' => $aanmelding]);
	}

	public function beoordeling($mid) {
		$maaltijd = $this->model->getMaaltijd($mid);
		$beoordeling = MaaltijdBeoordelingenModel::instance()->find('maaltijd_id = ? AND uid = ?', array($mid, LoginModel::getUid()))->fetch();
		if (!$beoordeling) {
			$beoordeling = MaaltijdBeoordelingenModel::instance()->nieuw($maaltijd);
		}
		$form = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
		if (!$form->validate()) {
			$form = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
		if ($form->validate()) {
			MaaltijdBeoordelingenModel::instance()->update($beoordeling);
		}
		return new JsonResponse(null);
	}

}
