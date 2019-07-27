<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\model\maalcie\CorveeHerinneringenModel;
use CsrDelft\model\maalcie\CorveeRepetitiesModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\CorveeToewijzenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\maalcie\corvee\taken\BeheerTaakView;
use CsrDelft\view\maalcie\corvee\taken\BeheerTakenLijstView;
use CsrDelft\view\maalcie\corvee\taken\BeheerTakenView;
use CsrDelft\view\maalcie\forms\RepetitieCorveeForm;
use CsrDelft\view\maalcie\forms\TaakForm;
use CsrDelft\view\maalcie\forms\ToewijzenForm;

/**
 * BeheerTakenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property CorveeTakenModel $model
 *
 */
class BeheerTakenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, CorveeTakenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => P_CORVEE_MOD,
				'prullenbak' => P_CORVEE_MOD,
				//'leegmaken' => P_MAAL_MOD,
				'maaltijd' => P_CORVEE_MOD,
				'herinneren' => P_CORVEE_MOD
			);
		} else {
			$this->acl = array(
				'nieuw' => P_CORVEE_MOD,
				'bewerk' => P_CORVEE_MOD,
				'opslaan' => P_CORVEE_MOD,
				'verwijder' => P_CORVEE_MOD,
				'herstel' => P_CORVEE_MOD,
				'toewijzen' => P_CORVEE_MOD,
				'puntentoekennen' => P_CORVEE_MOD,
				'puntenintrekken' => P_CORVEE_MOD,
				'email' => P_CORVEE_MOD,
				'aanmaken' => P_CORVEE_MOD
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$tid = null;
		if ($this->hasParam(3)) {
			$tid = (int)$this->getParam(3);
		}
		parent::performAction(array($tid));
	}

	public function beheer($tid = null, $mid = null) {
		$modal = null;
		if (is_int($tid) && $tid > 0) {
			$this->bewerk($tid);
			$modal = $this->view;
		} elseif (is_int($mid) && $mid > 0) {
			$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid, true);
			$taken = $this->model->getTakenVoorMaaltijd($mid, true);
		} else {
			$taken = $this->model->getAlleTaken();
			$maaltijd = null;
		}
		$this->view = new BeheerTakenView($taken, $maaltijd, false, CorveeRepetitiesModel::instance()->getAlleRepetities());
		$this->view = new CsrLayoutPage($this->view);
		$this->view->modal = $modal;
	}

	public function maaltijd($mid) {
		$this->beheer(null, $mid);
	}

	public function prullenbak() {
		$this->view = new BeheerTakenView($this->model->getVerwijderdeTaken(), null, true);
		$this->view = new CsrLayoutPage($this->view);
	}

	public function herinneren() {
		$verstuurd_errors = CorveeHerinneringenModel::stuurHerinneringen();
		$verstuurd = $verstuurd_errors[0];
		$errors = $verstuurd_errors[1];
		$aantal = sizeof($verstuurd);
		$count = sizeof($errors);
		if ($count > 0) {
			setMelding($count . ' herinnering' . ($count !== 1 ? 'en' : '') . ' niet kunnen versturen!', -1);
			foreach ($errors as $error) {
				setMelding($error->getMessage(), 2); // toon wat fout is gegaan
			}
		}
		if ($aantal > 0) {
			setMelding($aantal . ' herinnering' . ($aantal !== 1 ? 'en' : '') . ' verstuurd!', 1);
			foreach ($verstuurd as $melding) {
				setMelding($melding, 1); // toon wat goed is gegaan
			}
		} else {
			setMelding('Geen herinneringen verstuurd.', 0);
		}
		redirect(maalcieUrl);
	}

	public function nieuw($mid = null) {
		if ($mid !== null) {
			$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
			$beginDatum = $maaltijd->datum;
		}
		$crid = filter_input(INPUT_POST, 'crv_repetitie_id', FILTER_SANITIZE_NUMBER_INT);
		if (!empty($crid)) {
			$repetitie = CorveeRepetitiesModel::instance()->getRepetitie((int)$crid);
			if ($mid === null) {
				$beginDatum = CorveeRepetitiesModel::instance()->getFirstOccurrence($repetitie);
				if ($repetitie->periode_in_dagen > 0) {
					$this->view = new RepetitieCorveeForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
					return;
				}
			}
			$taak = CorveeTakenModel::instance()->vanRepetitie($repetitie, $beginDatum, $mid);
			$this->view = new TaakForm($taak, 'opslaan/0'); // fetches POST values itself
		} else {
			$taak = new CorveeTaak();
			if (isset($beginDatum)) {
				$taak->datum = $beginDatum;
			}
			$taak->maaltijd_id = $mid;
			$this->view = new TaakForm($taak, 'opslaan/0'); // fetches POST values itself
		}
	}

	public function bewerk($tid) {
		$taak = $this->model->getTaak($tid);
		$this->view = new TaakForm($taak, 'opslaan/' . $tid); // fetches POST values itself
	}

	public function opslaan($tid) {
		if ($tid > 0) {
			$this->bewerk($tid);
		} else {
			$this->nieuw();
		}
		if ($this->view->validate()) {
			/** @var CorveeTaak $values */
			$values = $this->view->getModel();
			$taak = $this->model->saveTaak((int)$tid, (int)$values->functie_id, $values->uid, $values->crv_repetitie_id, $values->maaltijd_id, $values->datum, $values->punten, $values->bonus_malus);
			$maaltijd = null;
			if (endsWith($_SERVER['HTTP_REFERER'], maalcieUrl . '/maaltijd/' . $values->maaltijd_id)) { // state of gui
				$maaltijd = MaaltijdenModel::instance()->getMaaltijd($values->maaltijd_id);
			}
			$this->view = new BeheerTaakView($taak, $maaltijd);
		}
	}

	public function verwijder($tid) {
		$this->model->verwijderTaak($tid);
		echo '<tr id="corveetaak-row-' . $tid . '" class="remove"></tr>';
		exit;
	}

	public function herstel($tid) {
		$this->model->herstelTaak($tid);
		echo '<tr id="corveetaak-row-' . $tid . '" class="remove"></tr>';
		exit;
	}

	public function toewijzen($tid) {
		$taak = $this->model->getTaak($tid);
		$uidField = new LidField('uid', null, null, 'leden'); // fetches POST values itself
		if ($uidField->validate()) {
			$taak = $this->model->getTaak($tid);
			$this->model->taakToewijzenAanLid($taak, $uidField->getValue());
			$this->view = new BeheerTaakView($taak);
		} else {
			$suggesties = CorveeToewijzenModel::getSuggesties($taak);
			$this->view = new ToewijzenForm($taak, $suggesties); // fetches POST values itself
		}
	}

	public function puntentoekennen($tid) {
		$taak = $this->model->getTaak($tid);
		$this->model->puntenToekennen($taak);
		$this->view = new BeheerTaakView($taak);
	}

	public function puntenintrekken($tid) {
		$taak = $this->model->getTaak($tid);
		$this->model->puntenIntrekken($taak);
		$this->view = new BeheerTaakView($taak);
	}

	public function email($tid) {
		$taak = $this->model->getTaak($tid);
		CorveeHerinneringenModel::stuurHerinnering($taak);
		$this->view = new BeheerTaakView($taak);
	}

	public function leegmaken() {
		$aantal = $this->model->prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' taak' : ' taken') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		redirect(maalcieUrl . '/prullenbak');
	}

	// Repetitie-Taken ############################################################

	public function aanmaken($crid) {
		$repetitie = CorveeRepetitiesModel::instance()->getRepetitie($crid);
		$form = new RepetitieCorveeForm($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$mid = (empty($values['maaltijd_id']) ? null : (int)$values['maaltijd_id']);
			$taken = $this->model->maakRepetitieTaken($repetitie, $values['begindatum'], $values['einddatum'], $mid);
			if (empty($taken)) {
				throw new CsrGebruikerException('Geen nieuwe taken aangemaakt.');
			}
			$this->view = new BeheerTakenLijstView($taken);
		} else {
			$this->view = $form;
		}
	}

}
