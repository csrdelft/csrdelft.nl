<?php


require_once 'taken/model/MaaltijdRepetitiesModel.class.php';
require_once 'taken/view/MaaltijdRepetitiesView.class.php';
require_once 'taken/view/forms/MaaltijdRepetitieFormView.class.php';

/**
 * MaaltijdRepetitiesController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdRepetitiesController extends AclController {

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
		$this->performAction(array($mrid));
	}
	
	public function beheer($mrid=null) {
		if (is_int($mrid) && $mrid > 0) {
			$this->bewerk($mrid);
		}
		$this->view = new MaaltijdRepetitiesView(MaaltijdRepetitiesModel::getAlleRepetities(), $this->getContent());
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->view->addScript('taken.js');
	}
	
	public function nieuw() {
		$repetitie = new MaaltijdRepetitie();
		$this->view = new MaaltijdRepetitieFormView($repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getStandaardTitel(), $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getIsAbonneerbaar(), $repetitie->getStandaardLimiet(), $repetitie->getAbonnementFilter()); // fetches POST values itself
	}
	
	public function bewerk($mrid) {
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		$this->view = new MaaltijdRepetitieFormView($repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getStandaardTitel(), $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getIsAbonneerbaar(), $repetitie->getStandaardLimiet(), $repetitie->getAbonnementFilter()); // fetches POST values itself
	}
	
	public function opslaan($mrid) {
		if ($mrid > 0) {
			$this->bewerk($mrid);
		}
		else {
			$this->view = new MaaltijdRepetitieFormView($mrid); // fetches POST values itself
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues(); 
			$repetitie_aantal = MaaltijdRepetitiesModel::saveRepetitie($mrid, $values['dag_vd_week'], $values['periode_in_dagen'], $values['standaard_titel'], $values['standaard_tijd'], $values['standaard_prijs'], $values['abonneerbaar'], $values['standaard_limiet'], $values['abonnement_filter']);
			$this->view = new MaaltijdRepetitiesView($repetitie_aantal[0]);
			if ($repetitie_aantal[1] > 0) {
				$this->view->setMelding($repetitie_aantal[1] .' abonnement'. ($repetitie_aantal[1] !== 1 ? 'en' : '') .' uitgeschakeld.', 2);
			}
		}
	}
	
	public function verwijder($mrid) {
		$aantal = MaaltijdRepetitiesModel::verwijderRepetitie($mrid);
		$this->view = new MaaltijdRepetitiesView($mrid);
		if ($aantal > 0) {
			$this->view->setMelding($aantal .' abonnement'. ($aantal !== 1 ? 'en' : '') .' uitgeschakeld.', 2);
		}
	}
	
	public function bijwerken($mrid) {
		$this->opslaan($mrid);
		if ($this->view instanceof MaaltijdRepetitiesView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$updated_aanmeldingen = MaaltijdenModel::updateRepetitieMaaltijden($this->view->getRepetitie(), $verplaats);
			$this->view->setMelding($updated_aanmeldingen[0] .' maaltijd'. ($updated_aanmeldingen[0] !== 1 ? 'en' : '') .' bijgewerkt'. ($verplaats ? ' en eventueel verplaatst.': '.'), 1);
			if ($updated_aanmeldingen[1] > 0) {
				$this->view->setMelding($updated_aanmeldingen[1] .' aanmelding'. ($updated_aanmeldingen[1] !== 1 ? 'en' : '') .' verwijderd vanwege aanmeldrestrictie: '. $this->view->getRepetitie()->getAbonnementFilter(), 2);
			}
		}
	}
}

?>