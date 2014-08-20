<?php

require_once 'maalcie/model/MaaltijdRepetitiesModel.class.php';
require_once 'maalcie/view/MaaltijdRepetitiesView.class.php';
require_once 'maalcie/view/forms/MaaltijdRepetitieForm.class.php';

/**
 * MaaltijdRepetitiesController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MaaltijdRepetitiesController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_MAAL_MOD'
			);
		} else {
			$this->acl = array(
				'nieuw'		 => 'P_MAAL_MOD',
				'bewerk'	 => 'P_MAAL_MOD',
				'opslaan'	 => 'P_MAAL_MOD',
				'verwijder'	 => 'P_MAAL_MOD',
				'bijwerken'	 => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mrid = null;
		if ($this->hasParam(3)) {
			$mrid = (int) $this->getParam(3);
		}
		parent::performAction(array($mrid));
	}

	public function beheer($mrid = null) {
		$popup = null;
		if (is_int($mrid) && $mrid > 0) {
			$this->bewerk($mrid);
			$popup = $this->getView();
		}
		$this->view = new MaaltijdRepetitiesView(MaaltijdRepetitiesModel::getAlleRepetities());
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
		$this->view->popup = $popup;
	}

	public function nieuw() {
		$repetitie = new MaaltijdRepetitie();
		$this->view = new MaaltijdRepetitieForm($repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getStandaardTitel(), $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getIsAbonneerbaar(), $repetitie->getStandaardLimiet(), $repetitie->getAbonnementFilter()); // fetches POST values itself
	}

	public function bewerk($mrid) {
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		$this->view = new MaaltijdRepetitieForm($repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getStandaardTitel(), $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getIsAbonneerbaar(), $repetitie->getStandaardLimiet(), $repetitie->getAbonnementFilter()); // fetches POST values itself
	}

	public function opslaan($mrid) {
		if ($mrid > 0) {
			$this->bewerk($mrid);
		} else {
			$this->view = new MaaltijdRepetitieForm($mrid); // fetches POST values itself
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$repetitie_aantal = MaaltijdRepetitiesModel::saveRepetitie($mrid, $values['dag_vd_week'], $values['periode_in_dagen'], $values['standaard_titel'], $values['standaard_tijd'], $values['standaard_prijs'], $values['abonneerbaar'], $values['standaard_limiet'], $values['abonnement_filter']);
			$this->view = new MaaltijdRepetitieView($repetitie_aantal[0]);
			if ($repetitie_aantal[1] > 0) {
				SimpleHTML::setMelding($repetitie_aantal[1] . ' abonnement' . ($repetitie_aantal[1] !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
			}
		}
	}

	public function verwijder($mrid) {
		$aantal = MaaltijdRepetitiesModel::verwijderRepetitie($mrid);
		if ($aantal > 0) {
			SimpleHTML::setMelding($aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
		}
		echo '<tr id="maalcie-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
		echo '<tr id="repetitie-row-' . $mrid . '" class="remove"></tr>';
		exit;
	}

	public function bijwerken($mrid) {
		$this->opslaan($mrid);
		if ($this->view instanceof MaaltijdRepetitiesView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$updated_aanmeldingen = MaaltijdenModel::updateRepetitieMaaltijden($this->view->getModel(), $verplaats);
			SimpleHTML::setMelding($updated_aanmeldingen[0] . ' maaltijd' . ($updated_aanmeldingen[0] !== 1 ? 'en' : '') . ' bijgewerkt' . ($verplaats ? ' en eventueel verplaatst.' : '.'), 1);
			if ($updated_aanmeldingen[1] > 0) {
				SimpleHTML::setMelding($updated_aanmeldingen[1] . ' aanmelding' . ($updated_aanmeldingen[1] !== 1 ? 'en' : '') . ' verwijderd vanwege aanmeldrestrictie: ' . $this->view->getModel()->getAbonnementFilter(), 2);
			}
		}
	}

}
