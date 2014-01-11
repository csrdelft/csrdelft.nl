<?php


require_once 'taken/model/CorveeRepetitiesModel.class.php';
require_once 'taken/model/MaaltijdRepetitiesModel.class.php';
require_once 'taken/view/CorveeRepetitiesView.class.php';
require_once 'taken/view/forms/CorveeRepetitieFormView.class.php';

/**
 * CorveeRepetitiesController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeRepetitiesController extends \AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD',
				'maaltijd' => 'P_CORVEE_MOD'
			);
		}
		else {
			$this->acl = array(
				'nieuw' => 'P_CORVEE_MOD',
				'bewerk' => 'P_CORVEE_MOD',
				'opslaan' => 'P_CORVEE_MOD',
				'verwijder' => 'P_CORVEE_MOD',
				'bijwerken' => 'P_MAAL_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$crid = null;
		if ($this->hasParam(3)) {
			$crid = intval($this->getParam(3));
		}
		$this->performAction(array($crid));
	}
	
	public function beheer($crid=null, $mrid=null) {
		$maaltijdrepetitie = null;
		if (is_int($crid) && $crid > 0) {
			$this->bewerk($crid);
			$repetities = CorveeRepetitiesModel::getAlleRepetities();
		}
		elseif (is_int($mrid) && $mrid > 0) {
			$repetities = CorveeRepetitiesModel::getRepetitiesVoorMaaltijdRepetitie($mrid);
			$maaltijdrepetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		}
		else {
			$repetities = CorveeRepetitiesModel::getAlleRepetities();
		}
		$this->content = new CorveeRepetitiesView($repetities, $maaltijdrepetitie, $this->getContent());
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function maaltijd($mrid) {
		$this->beheer(null, $mrid);
	}
	
	public function nieuw($mrid=null) {
		$repetitie = new CorveeRepetitie(0, $mrid);
		$this->content = new CorveeRepetitieFormView($repetitie->getCorveeRepetitieId(), $repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getFunctieId(), null, $repetitie->getStandaardAantal(), $repetitie->getIsVoorkeurbaar()); // fetches POST values itself
	}
	
	public function bewerk($crid) {
		$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
		$this->content = new CorveeRepetitieFormView($repetitie->getCorveeRepetitieId(), $repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getFunctieId(), $repetitie->getStandaardPunten(), $repetitie->getStandaardAantal(), $repetitie->getIsVoorkeurbaar()); // fetches POST values itself
	}
	
	public function opslaan($crid) {
		if ($crid > 0) {
			$this->bewerk($crid);
		}
		else {
			$this->content = new CorveeRepetitieFormView($crid); // fetches POST values itself
		}
		if ($this->content->validate()) {
			$values = $this->content->getValues(); 
			$mrid = ($values['mlt_repetitie_id'] === '' ? null : intval($values['mlt_repetitie_id']));
			$repetitie_aantal = CorveeRepetitiesModel::saveRepetitie($crid, $mrid, $values['dag_vd_week'], $values['periode_in_dagen'], intval($values['functie_id']), $values['standaard_punten'], $values['standaard_aantal'], $values['voorkeurbaar']);
			$maaltijdrepetitie = null;
			if (endsWith($_SERVER['HTTP_REFERER'], $GLOBALS['taken_module'] .'/maaltijd/'. $values['mlt_repetitie_id'])) { // state of gui
				$maaltijdrepetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
			}
			$this->content = new CorveeRepetitiesView($repetitie_aantal[0], $maaltijdrepetitie);
			if ($repetitie_aantal[1] > 0) {
				$this->content->setMelding($repetitie_aantal[1] .' voorkeur'. ($repetitie_aantal[1] !== 1 ? 'en' : '') .' uitgeschakeld.', 2);
			}
		}
	}
	
	public function verwijder($crid) {
		$aantal = CorveeRepetitiesModel::verwijderRepetitie($crid);
		$this->content = new CorveeRepetitiesView($crid);
		if ($aantal > 0) {
			$this->content->setMelding($aantal .' voorkeur'. ($aantal !== 1 ? 'en' : '') .' uitgeschakeld.', 2);
		}
	}
	
	public function bijwerken($crid) {
		$this->opslaan($crid);
		if ($this->content instanceof CorveeRepetitiesView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$aantal = TakenModel::updateRepetitieTaken($this->content->getRepetitie(), $verplaats);
			if ($aantal['update'] < $aantal['day']) {
				$aantal['update'] = $aantal['day'];
			}
			$this->content->setMelding(
				$aantal['update'] .' corveeta'. ($aantal['update'] !== 1 ? 'ken' : 'ak') .' bijgewerkt waarvan '.
				$aantal['day'] .' van dag verschoven.', 1);
			$aantal['datum'] += $aantal['maaltijd'];
			$this->content->setMelding(
				$aantal['datum'] .' corveeta'. ($aantal['datum'] !== 1 ? 'ken' : 'ak') .' aangemaakt waarvan '.
				$aantal['maaltijd'] .' maaltijdcorvee.', 1);
		}
	}
}

?>