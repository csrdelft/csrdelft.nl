<?php

require_once 'maalcie/model/MaaltijdenModel.class.php';
require_once 'maalcie/model/MaaltijdAanmeldingenModel.class.php';
require_once 'maalcie/model/MaaltijdRepetitiesModel.class.php';
require_once 'maalcie/view/BeheerMaaltijdenView.class.php';
require_once 'maalcie/view/forms/MaaltijdForm.class.php';
require_once 'maalcie/view/forms/RepetitieMaaltijdenForm.class.php';
require_once 'maalcie/view/forms/AanmeldingForm.class.php';

/**
 * BeheerMaaltijdenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerMaaltijdenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer'	 => 'P_MAAL_MOD',
				'prullenbak' => 'P_MAAL_MOD',
				//'leegmaken' => 'P_MAAL_MOD',
				'archief'	 => 'P_MAAL_MOD',
				'fiscaal'	 => 'P_MAAL_MOD'
			);
		} else {
			$this->acl = array(
				'sluit'			 => 'P_MAAL_MOD',
				'open'			 => 'P_MAAL_MOD',
				'nieuw'			 => 'P_MAAL_MOD',
				'bewerk'		 => 'P_MAAL_MOD',
				'opslaan'		 => 'P_MAAL_MOD',
				'verwijder'		 => 'P_MAAL_MOD',
				'herstel'		 => 'P_MAAL_MOD',
				'anderaanmelden' => 'P_MAAL_MOD',
				'anderafmelden'	 => 'P_MAAL_MOD',
				'aanmaken'		 => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mid = null;
		if ($this->hasParam(3)) {
			$mid = (int) $this->getParam(3);
		}
		parent::performAction(array($mid));
	}

	public function beheer($mid = null) {
		$modal = null;
		if (is_int($mid) && $mid > 0) {
			$this->bewerk($mid);
			$modal = $this->getView();
		}
		$body = new BeheerMaaltijdenView(MaaltijdenModel::getAlleMaaltijden(), false, false, MaaltijdRepetitiesModel::getAlleRepetities());
		$this->view = new CsrLayoutPage($body, array(), $modal);
		$this->view->addStylesheet('/layout/css/maalcie');
		$this->view->addScript('/layout/js/maalcie');
	}

	public function prullenbak() {
		$body = new BeheerMaaltijdenView(MaaltijdenModel::getVerwijderdeMaaltijden(), true);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('/layout/css/maalcie');
		$this->view->addScript('/layout/js/maalcie');
	}

	public function archief() {
		$body = new BeheerMaaltijdenView(MaaltijdenModel::getArchiefMaaltijdenTussen(), false, true);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('/layout/css/maalcie');
		$this->view->addScript('/layout/js/maalcie');
	}

	public function fiscaal($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
		$aanmeldingen = MaaltijdAanmeldingenModel::getAanmeldingenVoorMaaltijd($maaltijd);
		require_once 'maalcie/view/MaaltijdLijstView.class.php';
		$this->view = new MaaltijdLijstView($maaltijd, $aanmeldingen, null, true);
	}

	public function sluit($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		MaaltijdenModel::sluitMaaltijd($maaltijd);
		$this->view = new BeheerMaaltijdView($maaltijd);
	}

	public function open($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		MaaltijdenModel::openMaaltijd($maaltijd);
		$this->view = new BeheerMaaltijdView($maaltijd);
	}

	public function nieuw() {
		if (isset($_POST['mrid'])) {
			$mrid = (int) filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
			$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
			$beginDatum = MaaltijdRepetitiesModel::getFirstOccurrence($repetitie);
			if ($repetitie->getPeriodeInDagen() > 0) {
				$this->view = new RepetitieMaaltijdenForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
			} else {
				$this->view = new MaaltijdForm(0, $repetitie->getMaaltijdRepetitieId(), $repetitie->getStandaardTitel(), $repetitie->getStandaardLimiet(), $beginDatum, $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getAbonnementFilter()); // fetches POST values itself
			}
		} else {
			$maaltijd = new Maaltijd();
			$this->view = new MaaltijdForm($maaltijd->getMaaltijdId(), $maaltijd->getMaaltijdRepetitieId(), $maaltijd->getTitel(), $maaltijd->getAanmeldLimiet(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $maaltijd->getAanmeldFilter()); // fetches POST values itself
		}
	}

	public function bewerk($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		$this->view = new MaaltijdForm($maaltijd->getMaaltijdId(), $maaltijd->getMaaltijdRepetitieId(), $maaltijd->getTitel(), $maaltijd->getAanmeldLimiet(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $maaltijd->getAanmeldFilter()); // fetches POST values itself
	}

	public function opslaan($mid) {
		if ($mid > 0) {
			$this->bewerk($mid);
		} else {
			$this->view = new MaaltijdForm($mid); // fetches POST values itself
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$mrid = ($values['mlt_repetitie_id'] === 0 ? null : $values['mlt_repetitie_id']);
			$maaltijd_aanmeldingen = MaaltijdenModel::saveMaaltijd($mid, $mrid, $values['titel'], $values['aanmeld_limiet'], $values['datum'], $values['tijd'], $values['prijs'], $values['aanmeld_filter']);
			$this->view = new BeheerMaaltijdView($maaltijd_aanmeldingen[0]);
			if ($maaltijd_aanmeldingen[1] > 0) {
				setMelding($maaltijd_aanmeldingen[1] . ' aanmelding' . ($maaltijd_aanmeldingen[1] !== 1 ? 'en' : '') . ' verwijderd vanwege aanmeldrestrictie: ' . $maaltijd_aanmeldingen[0]->getAanmeldFilter(), 2);
			}
		}
	}

	public function verwijder($mid) {
		MaaltijdenModel::verwijderMaaltijd($mid);
		echo '<tr id="maaltijd-row-' . $mid . '" class="remove"></tr>';
		exit;
	}

	public function herstel($mid) {
		MaaltijdenModel::herstelMaaltijd($mid);
		echo '<tr id="maaltijd-row-' . $mid . '" class="remove"></tr>';
		exit;
	}

	public function anderaanmelden($mid) {
		$form = new AanmeldingForm($mid, true); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$aanmelding = MaaltijdAanmeldingenModel::aanmeldenVoorMaaltijd($mid, $values['voor_lid'], LoginModel::getUid(), $values['aantal_gasten'], true);
			$this->view = new BeheerMaaltijdView($aanmelding->getMaaltijd());
		} else {
			$this->view = $form;
		}
	}

	public function anderafmelden($mid) {
		$form = new AanmeldingForm($mid, false); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijd = MaaltijdAanmeldingenModel::afmeldenDoorLid($mid, $values['voor_lid'], true);
			$this->view = new BeheerMaaltijdView($maaltijd);
		} else {
			$this->view = $form;
		}
	}

	public function leegmaken() {
		$aantal = MaaltijdenModel::prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' maaltijd' : ' maaltijden') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		redirect(Instellingen::get('taken', 'url') . '/prullenbak');
	}

	// Repetitie-Maaltijden ############################################################

	public function aanmaken($mrid) {
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		$form = new RepetitieMaaltijdenForm($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijden = MaaltijdenModel::maakRepetitieMaaltijden($repetitie, strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (empty($maaltijden)) {
				throw new Exception('Geen nieuwe maaltijden aangemaakt');
			}
			$this->view = new BeheerMaaltijdenLijstView($maaltijden);
		} else {
			$this->view = $form;
		}
	}

}
