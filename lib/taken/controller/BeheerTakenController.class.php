<?php
namespace Taken\CRV;

require_once 'taken/model/TakenModel.class.php';
require_once 'taken/model/CorveeRepetitiesModel.class.php';
require_once 'taken/view/BeheerTakenView.class.php';
require_once 'taken/view/forms/TaakFormView.class.php';
require_once 'taken/view/forms/RepetitieCorveeFormView.class.php';

/**
 * BeheerTakenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerTakenController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD',
				'prullenbak' => 'P_CORVEE_MOD',
				'leegmaken' => 'P_MAAL_MOD',
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
				'koppelen' => 'P_CORVEE_MOD',
				'puntentoekennen' => 'P_CORVEE_MOD',
				'puntenintrekken' => 'P_CORVEE_MOD',
				'email' => 'P_CORVEE_MOD',
				'aanmaken' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$tid = null;
		if ($this->hasParam(2)) {
			$tid = intval($this->getParam(2));
		}
		$this->performAction($tid);
	}
	
	public function action_beheer($tid=null, $mid=null) {
		if (is_int($tid) && $tid > 0) {
			$this->action_bewerk($tid);
		}
		elseif (is_int($mid) && $mid > 0) {
			$taken = TakenModel::getTakenVoorMaaltijd($mid);
			$maaltijd = \Taken\MLT\MaaltijdenModel::getMaaltijd($mid, true);
		}
		else {
			$taken = TakenModel::getAlleTaken();
			$maaltijd = null;
		}
		$this->content = new BeheerTakenView($taken, $maaltijd, false, CorveeRepetitiesModel::getAlleRepetities(), $this->getContent());
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->content->addScript('taken.js');
	}
	
	public function action_maaltijd($mid) {
		$this->action_beheer(null, $mid);
	}
	
	public function action_prullenbak() {
		$this->content = new BeheerTakenView(TakenModel::getVerwijderdeTaken(), null, true);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_herinneren() {
		require_once 'taken/model/HerinneringenModel.class.php';
		$verstuurd_errors = HerinneringenModel::stuurHerinneringen();
		$verstuurd = $verstuurd_errors[0];
		$errors = $verstuurd_errors[1];
		$aantal = sizeof($errors);
		if ($aantal > 0) {
			setMelding($aantal .' herinnering'. ($aantal !== 1 ? 'en' : '') .' niet kunnen versturen!', -1);
			foreach ($errors as $error) {
				setMelding($error->getMessage(), 2); // toon wat er allemaal fout is gegaan
			}
		}
		if ($verstuurd > 0) {
			setMelding($verstuurd .' herinnering'. ($verstuurd !== 1 ? 'en' : '') .' verstuurd!', 1);
		}
		else {
			setMelding('Geen herinneringen verstuurd.', 0);
		}
		\SimpleHTML::invokeRefresh($GLOBALS['taken_module']);
	}
	
	public function action_nieuw($mid=null) {
		if (array_key_exists('crid', $_POST)) {
			$crid = intval($_POST['crid']);
			$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
			// start at first occurence
			$datum = time();
			$shift = $repetitie->getDagVanDeWeek() - date('w', $datum) + 7;
			$shift %= 7;
			if ($shift > 0) {
				$datum = strtotime('+'. $shift .' days', $datum);
			}
			$beginDatum = date('Y-m-d', $datum);
			if ($repetitie->getPeriodeInDagen() > 0) {
				$this->content = new RepetitieCorveeFormView($repetitie, $beginDatum, $beginDatum); // fetches POST values itself 
			}
			else {
				$functie = FunctiesModel::getFunctie($repetitie->getFunctieId());
				$this->content = new TaakFormView(0, $functie->getFunctieId(), null, $crid, $mid, $beginDatum, $functie->getStandaardPunten(), 0); // fetches POST values itself
			}
		}
		else {
			$taak = new CorveeTaak();
			$this->content = new TaakFormView($taak->getTaakId(), $taak->getFunctieId(), $taak->getLidId(), $taak->getCorveeRepetitieId(), $mid, $taak->getDatum(), $taak->getPunten(), $taak->getBonusMalus()); // fetches POST values itself
		}
	}
	
	public function action_bewerk($tid) {
		$taak = TakenModel::getTaak($tid);
		$this->content = new TaakFormView($taak->getTaakId(), $taak->getFunctieId(), $taak->getLidId(), $taak->getCorveeRepetitieId(), $taak->getMaaltijdId(), $taak->getDatum(), $taak->getPunten(), $taak->getBonusMalus()); // fetches POST values itself
	}
	
	public function action_opslaan($tid) {
		$form = new TaakFormView($tid); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$uid = ($values['lid_id'] === '' ? null : $values['lid_id']);
			$crid = ($values['crv_repetitie_id'] === '' ? null : intval($values['crv_repetitie_id']));
			$mid = ($values['maaltijd_id'] === '' ? null : intval($values['maaltijd_id']));
			$taak = TakenModel::saveTaak($tid, intval($values['functie_id']), $uid, $crid, $mid, $values['datum'], $values['punten'], $values['bonus_malus']);
			$maaltijd = null;
			if (endsWith($_SERVER['HTTP_REFERER'], $GLOBALS['taken_module'] .'/maaltijd/'. $values['maaltijd_id'])) { // state of gui
				$maaltijd = \Taken\MLT\MaaltijdenModel::getMaaltijd($mid);
			}
			$this->content = new BeheerTakenView($taak, $maaltijd);
		}
		else {
			$this->content = $form;
		}
	}
	
	public function action_verwijder($tid) {
		TakenModel::verwijderTaak($tid);
		$this->content = new BeheerTakenView($tid);
	}
	
	public function action_herstel($tid) {
		$taak = TakenModel::herstelTaak($tid);
		$this->content = new BeheerTakenView($taak->getTaakId());
	}
	
	public function action_toewijzen($tid) {
		$taak = TakenModel::getTaak($tid);
		$leden_punten = TakenModel::getSuggesties($taak);
		$voorkeuren = array();
		$repetitie = null;
		if ($taak->getCorveeRepetitieId() !== null) {
			require_once 'taken/model/VoorkeurenModel.class.php';
			$voorkeuren = VoorkeurenModel::getVoorkeurenVoorRepetitie($taak->getCorveeRepetitieId());
			$repetitie = CorveeRepetitiesModel::getRepetitie($taak->getCorveeRepetitieId());
		}
		require_once 'taken/view/forms/TaakToewijzenFormView.class.php';
		$form = new TaakToewijzenFormView($taak, $leden_punten, $voorkeuren, $repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$uid = ($values['lid_id'] === '' ? null : $values['lid_id']);
			TakenModel::taakToewijzenAanLid($taak, $uid);
			$this->content = new BeheerTakenView($taak);
		}
		else {
			$this->content = $form;
		}
	}
	
	//TODO
	public function action_koppelen($tid) {
		$taak = TakenModel::getTaak($tid);
		$form = new TaakKoppelenFormView($tid, $taak->getMaaltijdId()); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			TakenModel::taakKoppelenAanMaaltijd($taak, $values['maaltijd_id']);
			$this->content = new BeheerTakenView($taak);
		}
		else {
			$this->content = $form;
		}
	}
	
	public function action_puntentoekennen($tid) {
		$taak = TakenModel::getTaak($tid);
		TakenModel::puntenToekennen($taak);
		$this->content = new BeheerTakenView($taak);
	}
	
	public function action_puntenintrekken($tid) {
		$taak = TakenModel::getTaak($tid);
		TakenModel::puntenIntrekken($taak);
		$this->content = new BeheerTakenView($taak);
	}
	
	public function action_email($tid) {
		$taak = TakenModel::getTaak($tid);
		require_once 'taken/model/HerinneringenModel.class.php';
		HerinneringenModel::stuurHerinnering($taak);
		$this->content = new BeheerTakenView($taak);
	}
	
	public function action_leegmaken() {
		$aantal = TakenModel::prullenbakLeegmaken();
		\SimpleHTML::invokeRefresh($GLOBALS['taken_module'] .'/prullenbak', $aantal . ($aantal === 1 ? ' taak' : ' taken') .' definitief verwijderd.', ($aantal === 0 ? 0 : 1 ));
	}
	
	// Repetitie-Taken ############################################################
	
	public function action_aanmaken($crid) {
		$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
		$form = new RepetitieCorveeFormView($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$mid = ($values['maaltijd_id'] === '' ? null : intval($values['maaltijd_id']));
			$taken = TakenModel::maakRepetitieTaken($repetitie, $values['begindatum'], $values['einddatum'], $mid);
			if (empty($taken)) {
				throw new \Exception('Geen nieuwe taken aangemaakt');
			}
			$this->content = new BeheerTakenView($taken);
		}
		else {
			$this->content = $form;
		}
	}
}

?>