<?php
namespace Taken\MLT;

require_once 'taken/model/MaaltijdRepetitiesModel.class.php';
require_once 'taken/view/MaaltijdRepetitiesView.class.php';
require_once 'taken/view/forms/MaaltijdRepetitieFormView.class.php';

/**
 * MaaltijdRepetitiesController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdRepetitiesController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_MAAL_MOD'
			);
		}
		else {
			$this->acl = array(
				'nieuw' => 'P_MAAL_MOD',
				'bewerk' => 'P_MAAL_MOD',
				'opslaan' => 'P_MAAL_MOD',
				'verwijder' => 'P_MAAL_MOD',
				'bijwerken' => 'P_MAAL_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mrid = null;
		if ($this->hasParam(3)) {
			$mrid = intval($this->getParam(3));
		}
		$this->performAction($mrid);
	}
	
	public function action_beheer($mrid=null) {
		if (is_int($mrid) && $mrid > 0) {
			$this->action_bewerk($mrid);
		}
		$this->content = new MaaltijdRepetitiesView(MaaltijdRepetitiesModel::getAlleRepetities(), $this->getContent());
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->content->addScript('taken.js');
	}
	
	public function action_nieuw() {
		$repetitie = new MaaltijdRepetitie();
		$this->content = new MaaltijdRepetitieFormView($repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getStandaardTitel(), $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getIsAbonneerbaar(), $repetitie->getStandaardLimiet(), $repetitie->getAbonnementFilter()); // fetches POST values itself
	}
	
	public function action_bewerk($mrid) {
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		$this->content = new MaaltijdRepetitieFormView($repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getStandaardTitel(), $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getIsAbonneerbaar(), $repetitie->getStandaardLimiet(), $repetitie->getAbonnementFilter()); // fetches POST values itself
	}
	
	public function action_opslaan($mrid) {
		if ($mrid > 0) {
			$this->action_bewerk($mrid);
		}
		else {
			$this->content = new MaaltijdRepetitieFormView($mrid); // fetches POST values itself
		}
		if ($this->content->validate()) {
			$values = $this->content->getValues(); 
			$repetitie_aantal = MaaltijdRepetitiesModel::saveRepetitie($mrid, $values['dag_vd_week'], $values['periode_in_dagen'], $values['standaard_titel'], $values['standaard_tijd'], $values['standaard_prijs'], $values['abonneerbaar'], $values['standaard_limiet'], $values['abonnement_filter']);
			$this->content = new MaaltijdRepetitiesView($repetitie_aantal[0]);
			if ($repetitie_aantal[1] > 0) {
				$this->content->setMelding($repetitie_aantal[1] .' abonnement'. ($repetitie_aantal[1] !== 1 ? 'en' : '') .' uitgeschakeld.', 2);
			}
		}
	}
	
	public function action_verwijder($mrid) {
		$aantal = MaaltijdRepetitiesModel::verwijderRepetitie($mrid);
		$this->content = new MaaltijdRepetitiesView($mrid);
		if ($aantal > 0) {
			$this->content->setMelding($aantal .' abonnement'. ($aantal !== 1 ? 'en' : '') .' uitgeschakeld.', 2);
		}
	}
	
	public function action_bijwerken($mrid) {
		$this->action_opslaan($mrid);
		if ($this->content instanceof MaaltijdRepetitiesView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$updated_aanmeldingen = MaaltijdenModel::updateRepetitieMaaltijden($this->content->getRepetitie(), $verplaats);
			$this->content->setMelding($updated_aanmeldingen[0] .' maaltijd'. ($updated_aanmeldingen[0] !== 1 ? 'en' : '') .' bijgewerkt'. ($verplaats ? ' en eventueel verplaatst.': '.'), 1);
			if ($updated_aanmeldingen[1] > 0) {
				$this->content->setMelding($updated_aanmeldingen[1] .' aanmelding'. ($updated_aanmeldingen[1] !== 1 ? 'en' : '') .' verwijderd vanwege aanmeldrestrictie: '. $this->content->getRepetitie()->getAbonnementFilter(), 2);
			}
		}
	}
}

?>