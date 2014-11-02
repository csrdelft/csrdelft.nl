<?php

require_once 'maalcie/model/CorveeTakenModel.class.php';
require_once 'maalcie/model/CorveeRepetitiesModel.class.php';
require_once 'maalcie/view/BeheerTakenView.class.php';
require_once 'maalcie/view/forms/TaakForm.class.php';
require_once 'maalcie/view/forms/RepetitieCorveeForm.class.php';

/**
 * BeheerTakenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerTakenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer'	 => 'P_CORVEE_MOD',
				'prullenbak' => 'P_CORVEE_MOD',
				//'leegmaken' => 'P_MAAL_MOD',
				'maaltijd'	 => 'P_CORVEE_MOD',
				'herinneren' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'nieuw'				 => 'P_CORVEE_MOD',
				'bewerk'			 => 'P_CORVEE_MOD',
				'opslaan'			 => 'P_CORVEE_MOD',
				'verwijder'			 => 'P_CORVEE_MOD',
				'herstel'			 => 'P_CORVEE_MOD',
				'toewijzen'			 => 'P_CORVEE_MOD',
				'puntentoekennen'	 => 'P_CORVEE_MOD',
				'puntenintrekken'	 => 'P_CORVEE_MOD',
				'email'				 => 'P_CORVEE_MOD',
				'aanmaken'			 => 'P_CORVEE_MOD'
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
			$tid = (int) $this->getParam(3);
		}
		parent::performAction(array($tid));
	}

	public function beheer($tid = null, $mid = null) {
		$modal = null;
		if (is_int($tid) && $tid > 0) {
			$this->bewerk($tid);
			$modal = $this->getView();
		} elseif (is_int($mid) && $mid > 0) {
			$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
			$taken = CorveeTakenModel::getTakenVoorMaaltijd($mid, true);
		} else {
			$taken = CorveeTakenModel::getAlleTaken();
			$maaltijd = null;
		}
		$this->view = new BeheerTakenView($taken, $maaltijd, false, CorveeRepetitiesModel::getAlleRepetities());
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet($this->view->getCompressedStyleUrl('layout', 'maalcie'), true);
		$this->view->addScript($this->view->getCompressedScriptUrl('layout', 'maalcie'), true);
		$this->view->modal = $modal;
	}

	public function maaltijd($mid) {
		$this->beheer(null, $mid);
	}

	public function prullenbak() {
		$this->view = new BeheerTakenView(CorveeTakenModel::getVerwijderdeTaken(), null, true);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet($this->view->getCompressedStyleUrl('layout', 'maalcie'), true);
		$this->view->addScript($this->view->getCompressedScriptUrl('layout', 'maalcie'), true);
	}

	public function herinneren() {
		require_once 'maalcie/model/CorveeHerinneringenModel.class.php';
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
			$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			$beginDatum = $maaltijd->getDatum();
		}
		if (isset($_POST['crid'])) {
			$crid = (int) filter_input(INPUT_POST, 'crid', FILTER_SANITIZE_NUMBER_INT);
			$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
			if ($mid === null) {
				$beginDatum = CorveeRepetitiesModel::getFirstOccurrence($repetitie);
				if ($repetitie->getPeriodeInDagen() > 0) {
					$this->view = new RepetitieCorveeForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
					return;
				}
			}
			$this->view = new TaakForm(0, $repetitie->getFunctieId(), null, $crid, $mid, $beginDatum, $repetitie->getStandaardPunten(), 0); // fetches POST values itself
		} else {
			$taak = new CorveeTaak();
			if (isset($beginDatum)) {
				$taak->setDatum($beginDatum);
			}
			$this->view = new TaakForm($taak->getTaakId(), $taak->getFunctieId(), $taak->getUid(), $taak->getCorveeRepetitieId(), $mid, $taak->getDatum(), null, $taak->getBonusMalus()); // fetches POST values itself
		}
	}

	public function bewerk($tid) {
		$taak = CorveeTakenModel::getTaak($tid);
		$this->view = new TaakForm($taak->getTaakId(), $taak->getFunctieId(), $taak->getUid(), $taak->getCorveeRepetitieId(), $taak->getMaaltijdId(), $taak->getDatum(), $taak->getPunten(), $taak->getBonusMalus()); // fetches POST values itself
	}

	public function opslaan($tid) {
		if ($tid > 0) {
			$this->bewerk($tid);
		} else {
			$this->view = new TaakForm($tid); // fetches POST values itself
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$uid = ($values['uid'] === '' ? null : $values['uid']);
			$mid = ($values['maaltijd_id'] === 0 ? null : $values['maaltijd_id']);
			$crid = ($values['crv_repetitie_id'] === 0 ? null : $values['crv_repetitie_id']);
			$taak = CorveeTakenModel::saveTaak($tid, intval($values['functie_id']), $uid, $crid, $mid, $values['datum'], intval($values['punten']), intval($values['bonus_malus']));
			$maaltijd = null;
			if (endsWith($_SERVER['HTTP_REFERER'], maalcieUrl . '/maaltijd/' . $values['maaltijd_id'])) { // state of gui
				$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			}
			$this->view = new BeheerTaakView($taak, $maaltijd);
		}
	}

	public function verwijder($tid) {
		CorveeTakenModel::verwijderTaak($tid);
		echo '<tr id="corveetaak-row-' . $tid . '" class="remove"></tr>';
		exit;
	}

	public function herstel($tid) {
		CorveeTakenModel::herstelTaak($tid);
		echo '<tr id="corveetaak-row-' . $tid . '" class="remove"></tr>';
		exit;
	}

	public function toewijzen($tid) {
		$taak = CorveeTakenModel::getTaak($tid);
		$InputField = new LidField('uid', null, null, 'leden'); // fetches POST values itself
		if ($InputField->validate()) {
			$uid = $InputField->getValue();
			if ($uid === '') {
				$uid = null;
			}
			$taak = CorveeTakenModel::getTaak($tid);
			CorveeTakenModel::taakToewijzenAanLid($taak, $uid);
			$this->view = new BeheerTaakView($taak);
		} else {
			require_once 'maalcie/model/CorveeToewijzenModel.class.php';
			require_once 'maalcie/view/forms/ToewijzenForm.class.php';

			$suggesties = CorveeToewijzenModel::getSuggesties($taak);
			$this->view = new ToewijzenForm($taak, $suggesties); // fetches POST values itself
		}
	}

	public function puntentoekennen($tid) {
		$taak = CorveeTakenModel::getTaak($tid);
		CorveeTakenModel::puntenToekennen($taak);
		$this->view = new BeheerTaakView($taak);
	}

	public function puntenintrekken($tid) {
		$taak = CorveeTakenModel::getTaak($tid);
		CorveeTakenModel::puntenIntrekken($taak);
		$this->view = new BeheerTaakView($taak);
	}

	public function email($tid) {
		$taak = CorveeTakenModel::getTaak($tid);
		require_once 'maalcie/model/CorveeHerinneringenModel.class.php';
		CorveeHerinneringenModel::stuurHerinnering($taak);
		$this->view = new BeheerTaakView($taak);
	}

	public function leegmaken() {
		$aantal = CorveeTakenModel::prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' taak' : ' taken') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		redirect(maalcieUrl . '/prullenbak');
	}

	// Repetitie-Taken ############################################################

	public function aanmaken($crid) {
		$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
		$form = new RepetitieCorveeForm($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$mid = ($values['maaltijd_id'] === '' ? null : intval($values['maaltijd_id']));
			$taken = CorveeTakenModel::maakRepetitieTaken($repetitie, $values['begindatum'], $values['einddatum'], $mid);
			if (empty($taken)) {
				throw new Exception('Geen nieuwe taken aangemaakt');
			}
			$this->view = new BeheerTakenLijstView($taken);
		} else {
			$this->view = $form;
		}
	}

}
