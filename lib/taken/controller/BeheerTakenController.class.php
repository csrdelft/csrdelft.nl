<?php


require_once 'MVC/controller/AclController.abstract.php';
require_once 'taken/model/TakenModel.class.php';
require_once 'taken/model/CorveeRepetitiesModel.class.php';
require_once 'taken/view/BeheerTakenView.class.php';
require_once 'taken/view/forms/TaakFormView.class.php';
require_once 'taken/view/forms/RepetitieCorveeFormView.class.php';

/**
 * BeheerTakenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerTakenController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD',
				'prullenbak' => 'P_CORVEE_MOD',
				//'leegmaken' => 'P_MAAL_MOD',
				'maaltijd' => 'P_CORVEE_MOD',
				'herinneren' => 'P_CORVEE_MOD'
			);
		}
		else {
			$this->acl = array(
				'nieuw' => 'P_CORVEE_MOD',
				'bewerk' => 'P_CORVEE_MOD',
				'opslaan' => 'P_CORVEE_MOD',
				'verwijder' => 'P_CORVEE_MOD',
				'herstel' => 'P_CORVEE_MOD',
				'toewijzen' => 'P_CORVEE_MOD',
				'puntentoekennen' => 'P_CORVEE_MOD',
				'puntenintrekken' => 'P_CORVEE_MOD',
				'email' => 'P_CORVEE_MOD',
				'aanmaken' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$tid = null;
		if ($this->hasParam(3)) {
			$tid = intval($this->getParam(3));
		}
		$this->performAction(array($tid));
	}
	
	public function beheer($tid=null, $mid=null) {
		if (is_int($tid) && $tid > 0) {
			$this->bewerk($tid);
		}
		elseif (is_int($mid) && $mid > 0) {
			$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
			$taken = TakenModel::getTakenVoorMaaltijd($mid, true);
		}
		else {
			$taken = TakenModel::getAlleTaken();
			$maaltijd = null;
		}
		$this->view = new BeheerTakenView($taken, $maaltijd, false, CorveeRepetitiesModel::getAlleRepetities(), $this->getContent());
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->view->addScript('taken.js');
	}
	
	public function maaltijd($mid) {
		$this->beheer(null, $mid);
	}
	
	public function prullenbak() {
		$this->view = new BeheerTakenView(TakenModel::getVerwijderdeTaken(), null, true);
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}
	
	public function herinneren() {
		require_once 'taken/model/HerinneringenModel.class.php';
		$verstuurd_errors = HerinneringenModel::stuurHerinneringen();
		$verstuurd = $verstuurd_errors[0];
		$errors = $verstuurd_errors[1];
		$aantal = sizeof($verstuurd);
		$count = sizeof($errors);
		if ($count > 0) {
			setMelding($count .' herinnering'. ($count !== 1 ? 'en' : '') .' niet kunnen versturen!', -1);
			foreach ($errors as $error) {
				setMelding($error->getMessage(), 2); // toon wat fout is gegaan
			}
		}
		if ($aantal > 0) {
			setMelding($aantal .' herinnering'. ($aantal !== 1 ? 'en' : '') .' verstuurd!', 1);
			foreach ($verstuurd as $melding) {
				setMelding($melding, 1); // toon wat goed is gegaan
			}
		}
		else {
			setMelding('Geen herinneringen verstuurd.', 0);
		}
		invokeRefresh(Instellingen::get('taken', 'url'));
	}
	
	public function nieuw($mid=null) {
		if ($mid !== null) {
			$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			$beginDatum = $maaltijd->getDatum();
		}
		if (array_key_exists('crid', $_POST)) {
			$crid = (int) filter_input(INPUT_POST, 'crid', FILTER_SANITIZE_NUMBER_INT);
			$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
			if ($mid === null) {
				// start at first occurence
				$datum = time();
				$shift = $repetitie->getDagVanDeWeek() - date('w', $datum) + 7;
				$shift %= 7;
				if ($shift > 0) {
					$datum = strtotime('+'. $shift .' days', $datum);
				}
				$beginDatum = date('Y-m-d', $datum);
				
				if ($repetitie->getPeriodeInDagen() > 0) {
					$this->view = new RepetitieCorveeFormView($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
					return;
				}
			}
			$this->view = new TaakFormView(0, $repetitie->getFunctieId(), null, $crid, $mid, $beginDatum, $repetitie->getStandaardPunten(), 0); // fetches POST values itself
		}
		else {
			$taak = new CorveeTaak();
			if (isset($beginDatum)) {
				$taak->setDatum($beginDatum);
			}
			$this->view = new TaakFormView($taak->getTaakId(), $taak->getFunctieId(), $taak->getLidId(), $taak->getCorveeRepetitieId(), $mid, $taak->getDatum(), null, $taak->getBonusMalus()); // fetches POST values itself
		}
	}
	
	public function bewerk($tid) {
		$taak = TakenModel::getTaak($tid);
		$this->view = new TaakFormView($taak->getTaakId(), $taak->getFunctieId(), $taak->getLidId(), $taak->getCorveeRepetitieId(), $taak->getMaaltijdId(), $taak->getDatum(), $taak->getPunten(), $taak->getBonusMalus()); // fetches POST values itself
	}
	
	public function opslaan($tid) {
		if ($tid > 0) {
			$this->bewerk($tid);
		}
		else {
			$this->view = new TaakFormView($tid); // fetches POST values itself
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$uid = ($values['lid_id'] === '' ? null : $values['lid_id']);
			$crid = ($values['crv_repetitie_id'] === '' ? null : intval($values['crv_repetitie_id']));
			$mid = ($values['maaltijd_id'] === 0 ? null : $values['maaltijd_id']);
			$taak = TakenModel::saveTaak($tid, intval($values['functie_id']), $uid, $crid, $mid, $values['datum'], intval($values['punten']), intval($values['bonus_malus']));
			$maaltijd = null;
			if (endsWith($_SERVER['HTTP_REFERER'], Instellingen::get('taken', 'url') .'/maaltijd/'. $values['maaltijd_id'])) { // state of gui
				$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			}
			$this->view = new BeheerTakenView($taak, $maaltijd);
		}
	}
	
	public function verwijder($tid) {
		TakenModel::verwijderTaak($tid);
		$this->view = new BeheerTakenView($tid);
	}
	
	public function herstel($tid) {
		$taak = TakenModel::herstelTaak($tid);
		$this->view = new BeheerTakenView($taak->getTaakId());
	}
	
	public function toewijzen($tid) {
		$taak = TakenModel::getTaak($tid);
		$InputField = new LidField('lid_id', null, null, 'leden'); // fetches POST values itself
		if ($InputField->validate()) {
			$uid = $InputField->getValue();
			if ($uid === '') {
				$uid = null;
			}
			$taak = TakenModel::getTaak($tid);
			TakenModel::taakToewijzenAanLid($taak, $uid);
			$this->view = new BeheerTakenView($taak);
		}
		else {
			require_once 'taken/model/ToewijzenModel.class.php';
			require_once 'taken/view/forms/ToewijzenFormView.class.php';
			
			$suggesties = ToewijzenModel::getSuggesties($taak);
			$this->view = new ToewijzenFormView($taak, $suggesties); // fetches POST values itself
		}
	}
	
	public function puntentoekennen($tid) {
		$taak = TakenModel::getTaak($tid);
		TakenModel::puntenToekennen($taak);
		$this->view = new BeheerTakenView($taak);
	}
	
	public function puntenintrekken($tid) {
		$taak = TakenModel::getTaak($tid);
		TakenModel::puntenIntrekken($taak);
		$this->view = new BeheerTakenView($taak);
	}
	
	public function email($tid) {
		$taak = TakenModel::getTaak($tid);
		require_once 'taken/model/HerinneringenModel.class.php';
		HerinneringenModel::stuurHerinnering($taak);
		$this->view = new BeheerTakenView($taak);
	}
	
	public function leegmaken() {
		$aantal = TakenModel::prullenbakLeegmaken();
		invokeRefresh(Instellingen::get('taken', 'url') .'/prullenbak', $aantal . ($aantal === 1 ? ' taak' : ' taken') .' definitief verwijderd.', ($aantal === 0 ? 0 : 1 ));
	}
	
	// Repetitie-Taken ############################################################
	
	public function aanmaken($crid) {
		$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
		$form = new RepetitieCorveeFormView($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$mid = ($values['maaltijd_id'] === '' ? null : intval($values['maaltijd_id']));
			$taken = TakenModel::maakRepetitieTaken($repetitie, $values['begindatum'], $values['einddatum'], $mid);
			if (empty($taken)) {
				throw new Exception('Geen nieuwe taken aangemaakt');
			}
			$this->view = new BeheerTakenView($taken);
		}
		else {
			$this->view = $form;
		}
	}
}

?>