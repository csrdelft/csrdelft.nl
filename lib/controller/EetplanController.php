<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\eetplan\EetplanBekendenModel;
use CsrDelft\model\eetplan\EetplanModel;
use CsrDelft\model\entity\eetplan\Eetplan;
use CsrDelft\model\entity\eetplan\EetplanBekenden;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\eetplan\EetplanBekendeHuizenForm;
use CsrDelft\view\eetplan\EetplanBekendeHuizenResponse;
use CsrDelft\view\eetplan\EetplanBekendeHuizenTable;
use CsrDelft\view\eetplan\EetplanBekendenForm;
use CsrDelft\view\eetplan\EetplanBekendenTable;
use CsrDelft\view\eetplan\EetplanHuizenResponse;
use CsrDelft\view\eetplan\EetplanHuizenTable;
use CsrDelft\view\eetplan\EetplanHuizenZoekenResponse;
use CsrDelft\view\eetplan\EetplanRelatieResponse;
use CsrDelft\view\eetplan\NieuwEetplanForm;
use CsrDelft\view\eetplan\VerwijderEetplanForm;
use CsrDelft\view\View;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor eetplan.
 */
class EetplanController {
	/** @var string */
	private $lichting;
	/** @var EetplanModel */
	private $eetplanModel;
	/** @var EetplanBekendenModel */
	private $eetplanBekendenModel;
	/** @var WoonoordenModel */
	private $woonoordenModel;

	public function __construct() {
		$this->eetplanModel = EetplanModel::instance();
		$this->eetplanBekendenModel = EetplanBekendenModel::instance();
		$this->woonoordenModel = WoonoordenModel::instance();
		$this->lichting = substr((string)LichtingenModel::getJongsteLidjaar(), 2, 2);
	}

	public function view() {
		return view('eetplan.overzicht', [
			'eetplan' => $this->eetplanModel->getEetplan($this->lichting)
		]);
	}

	/**
	 * @param null $uid
	 * @return View
	 * @throws CsrToegangException
	 */
	public function noviet($uid = null) {
		$eetplan = $this->eetplanModel->getEetplanVoorNoviet($uid);
		if ($eetplan === false) {
			throw new CsrToegangException("Geen eetplan gevonden voor deze noviet", 404);
		}

		return view('eetplan.noviet', [
			'noviet' => ProfielModel::get($uid),
			'eetplan' => $this->eetplanModel->getEetplanVoorNoviet($uid)
		]);
	}

	public function huis($id = null) {
		$eetplan = $this->eetplanModel->getEetplanVoorHuis($id, $this->lichting);
		if ($eetplan == []) {
			throw new CsrGebruikerException('Huis niet gevonden', 404);
		}

		return view('eetplan.huis', [
			'woonoord' => WoonoordenModel::get($id),
			'eetplan' => $eetplan,
		]);
	}

	public function woonoorden($actie = null) {
		if ($actie == 'toggle') {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$woonoorden = [];
			foreach ($selection as $woonoord) {
				/** @var Woonoord $woonoord */
				$woonoord = $this->woonoordenModel->retrieveByUUID($woonoord);
				$woonoord->eetplan = !$woonoord->eetplan;
				$this->woonoordenModel->update($woonoord);
				$woonoorden[] = $woonoord;
			}
			return new EetplanHuizenResponse($woonoorden);
		} else {
			$woonoorden = $this->woonoordenModel->find('status = ?', array(GroepStatus::HT));
			return new EetplanHuizenResponse($woonoorden);
		}
	}

	/**
	 * @return View
	 * @throws CsrToegangException
	 */
	public function bekendehuizen() {
		return new EetplanBekendeHuizenResponse($this->eetplanModel->getBekendeHuizen($this->lichting));
	}

	public function bekendehuizen_toevoegen() {
		$eetplan = new Eetplan();
		$eetplan->avond = '0000-00-00';
		$form = new EetplanBekendeHuizenForm($eetplan, '/eetplan/bekendehuizen/toevoegen');
		if (!$form->validate()) {
			return $form;
		} elseif ($this->eetplanModel->exists($eetplan)) {
			setMelding('Deze noviet is al eens op dit huis geweest', -1);
			return $form;
		} else {
			$this->eetplanModel->create($eetplan);
			return new EetplanBekendeHuizenResponse($this->eetplanModel->getBekendeHuizen($this->lichting));
		}
	}

	public function bekendehuizen_bewerken($uuid = null) {
		if (!$uuid) {
			$uuid = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY)[0];
		}

		$eetplan = $this->eetplanModel->retrieveByUUID($uuid);
		$form = new EetplanBekendeHuizenForm($eetplan, '/eetplan/bekendehuizen/bewerken/' . $uuid, true);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanModel->update($eetplan);
			return new EetplanBekendeHuizenResponse($this->eetplanModel->getBekendeHuizen($this->lichting));
		} else {
			return $form;
		}
	}

	public function bekendehuizen_verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$verwijderd = array();
		if ($selection !== false) {
			foreach ($selection as $uuid) {
				$eetplan = $this->eetplanModel->retrieveByUUID($uuid);
				if ($eetplan === false) continue;
				$this->eetplanModel->delete($eetplan);
				$verwijderd[] = $eetplan;
			}
		}
		return new RemoveRowsResponse($verwijderd);
	}

	public function bekendehuizen_zoeken() {
		$huisnaam = filter_input(INPUT_GET, 'q');
		$huisnaam = '%' . $huisnaam . '%';
		$woonoorden = $this->woonoordenModel->find('status = ? AND naam LIKE ?', array(GroepStatus::HT, $huisnaam))->fetchAll();
		return new EetplanHuizenZoekenResponse($woonoorden);
	}

	public function novietrelatie($actie = null) {
		return new EetplanRelatieResponse($this->eetplanBekendenModel->getBekenden($this->lichting));
	}

	public function novietrelatie_toevoegen() {
		$eetplanbekenden = new EetplanBekenden();
		$form = new EetplanBekendenForm($eetplanbekenden, '/eetplan/novietrelatie/toevoegen');
		if (!$form->validate()) {
			return $form;
		} elseif ($this->eetplanBekendenModel->exists($eetplanbekenden)) {
			setMelding('Bekenden bestaan al', -1);
			return $form;
		} else {
			$this->eetplanBekendenModel->create($eetplanbekenden);
			return new EetplanRelatieResponse($this->eetplanBekendenModel->getBekenden($this->lichting));
		}
	}

	public function novietrelatie_bewerken($uuid) {
		if (!$uuid) {
			$uuid = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY)[0];
		}

		$eetplanbekenden = $this->eetplanBekendenModel->retrieveByUUID($uuid);
		$form = new EetplanBekendenForm($eetplanbekenden, '/eetplan/novietrelatie/bewerken/' . $uuid, true);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanBekendenModel->update($eetplanbekenden);
			return new EetplanRelatieResponse($this->eetplanBekendenModel->getBekenden($this->lichting));
		} else {
			return $form;
		}
	}

	public function novietrelatie_verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$verwijderd = array();
		foreach ($selection as $uuid) {
			$bekenden = $this->eetplanBekendenModel->retrieveByUUID($uuid);
			$this->eetplanBekendenModel->delete($bekenden);
			$verwijderd[] = $bekenden;
		}
		return new RemoveRowsResponse($verwijderd);
	}

	/**
	 * Beheerpagina.
	 *
	 * POST een json body om dingen te doen.
	 */
	public function beheer() {
		return view('eetplan.beheer', [
			'bekendentable' => new EetplanBekendenTable(),
			'huizentable' => new EetplanHuizenTable(),
			'bekendehuizentable' => new EetplanBekendeHuizenTable(),
			'eetplan' => $this->eetplanModel->getEetplan($this->lichting)
		]);
	}

	public function nieuw() {
		$form = new NieuwEetplanForm();

		if (!$form->validate()) {
			return $form;
		} elseif ($this->eetplanModel->count("avond = ?", array($form->getValues()['avond'])) > 0) {
			setMelding('Er bestaat al een eetplan met deze datum', -1);
			return $form;
		} else {
			$avond = $form->getValues()['avond'];
			$eetplan = $this->eetplanModel->maakEetplan($avond, $this->lichting);

			foreach ($eetplan as $sessie) {
				$this->eetplanModel->create($sessie);
			}

			return view('eetplan.table', ['eetplan' => $this->eetplanModel->getEetplan($this->lichting)]);
		}
	}

	public function verwijderen() {
		$avonden = $this->eetplanModel->getAvonden($this->lichting);
		$form = new VerwijderEetplanForm($avonden);

		if (!$form->validate()) {
			return $form;
		} else {
			$avond = $form->getValues()['avond'];
			$this->eetplanModel->verwijderEetplan($avond, $this->lichting);

			return view('eetplan.table', ['eetplan' => $this->eetplanModel->getEetplan($this->lichting)]);
		}
	}
}
