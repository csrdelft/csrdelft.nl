<?php
namespace Taken\MLT;

require_once 'taken/model/MaaltijdenModel.class.php';
require_once 'taken/model/AanmeldingenModel.class.php';
require_once 'taken/model/MaaltijdRepetitiesModel.class.php';
require_once 'taken/view/BeheerMaaltijdenView.class.php';
require_once 'taken/view/MaaltijdLijstView.class.php';
require_once 'taken/view/forms/MaaltijdFormView.class.php';
require_once 'taken/view/forms/RepetitieMaaltijdenFormView.class.php';
require_once 'taken/view/forms/AanmeldingFormView.class.php';

/**
 * BeheerMaaltijdenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerMaaltijdenController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_MAAL_MOD',
				'prullenbak' => 'P_MAAL_MOD',
				'lijst' => 'P_MAAL_IK',
				'fiscaal' => 'P_MAAL_MOD'
			);
		}
		else {
			$this->acl = array(
				'sluit' => 'P_MAAL_IK',
				'open' => 'P_MAAL_MOD',
				'nieuw' => 'P_MAAL_MOD',
				'bewerk' => 'P_MAAL_MOD',
				'opslaan' => 'P_MAAL_MOD',
				'verwijder' => 'P_MAAL_MOD',
				'herstel' => 'P_MAAL_MOD',
				'anderaanmelden' => 'P_MAAL_MOD',
				'anderafmelden' => 'P_MAAL_MOD',
				'aanmaken' => 'P_MAAL_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$mid = null;
		if ($this->hasParam(2)) {
			$mid = intval($this->getParam(2));
		}
		$this->performAction($mid);
	}
	
	public function action_beheer($mid=null) {
		if (is_int($mid) && $mid > 0) {
			$this->action_bewerk($mid);
		}
		$this->content = new BeheerMaaltijdenView(MaaltijdenModel::getAlleMaaltijden(), false, MaaltijdRepetitiesModel::getAlleRepetities(), $this->getContent());
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->content->addScript('taken.js');
	}
	
	public function action_prullenbak() {
		$this->content = new BeheerMaaltijdenView(MaaltijdenModel::getVerwijderdeMaaltijden(), true);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_lijst($mid) {
		if (!(opConfide() || \LoginLid::instance()->hasPermission('P_MAAL_MOD'))) {
			$this->action_geentoegang();
			return;
		}
		$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
		$aanmeldingen = AanmeldingenModel::getAanmeldingenVoorMaaltijdLijst($maaltijd);
		$taken = \Taken\CRV\TakenModel::getTakenVoorMaaltijd($mid);
		$this->content = new MaaltijdLijstView($maaltijd, $aanmeldingen, $taken);
	}
	
	public function action_fiscaal($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
		$aanmeldingen = AanmeldingenModel::getAanmeldingenVoorMaaltijdLijst($maaltijd);
		$this->content = new MaaltijdLijstView($maaltijd, $aanmeldingen, null, true);
	}
	
	public function action_sluit($mid) {
		if (!(opConfide() || \LoginLid::instance()->hasPermission('P_MAAL_MOD'))) {
			$this->action_geentoegang();
			return;
		}
		$maaltijd = MaaltijdenModel::sluitMaaltijd($mid);
		$this->content = new BeheerMaaltijdenView($maaltijd);
	}
	
	public function action_open($mid) {
		$maaltijd = MaaltijdenModel::openMaaltijd($mid);
		$this->content = new BeheerMaaltijdenView($maaltijd);
	}
	
	public function action_nieuw() {
		if (array_key_exists('mrid', $_POST)) {
			$mrid = intval($_POST['mrid']);
			$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
			// start at first occurence
			$datum = time();
			$shift = $repetitie->getDagVanDeWeek() - date('w', $datum) + 7;
			$shift %= 7;
			if ($shift > 0) {
				$datum = strtotime('+'. $shift .' days', $datum);
			}
			$beginDatum = date('Y-m-d', $datum);
			if ($repetitie->getPeriodeInDagen() > 0) {
				$this->content = new RepetitieMaaltijdenFormView($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
			}
			else {
				$this->content = new MaaltijdFormView(0, $repetitie->getMaaltijdRepetitieId(), $repetitie->getStandaardTitel(), intval($repetitie->getStandaardLimiet()), $beginDatum, $repetitie->getStandaardTijd(), $repetitie->getStandaardPrijs(), $repetitie->getAbonnementFilter());
			}
		}
		else {
			$maaltijd = new Maaltijd();
			$this->content = new MaaltijdFormView($maaltijd->getMaaltijdId(), $maaltijd->getMaaltijdRepetitieId(), $maaltijd->getTitel(), $maaltijd->getAanmeldLimiet(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $maaltijd->getAanmeldFilter());
		}
	}
	
	public function action_bewerk($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		$this->content = new MaaltijdFormView($maaltijd->getMaaltijdId(), $maaltijd->getMaaltijdRepetitieId(), $maaltijd->getTitel(), $maaltijd->getAanmeldLimiet(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $maaltijd->getAanmeldFilter());
	}
	
	public function action_opslaan($mid) {
		$form = new MaaltijdFormView($mid); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$mrid = ($values['mlt_repetitie_id'] === '' ? null : intval($values['mlt_repetitie_id']));
			$maaltijd_aanmeldingen = MaaltijdenModel::saveMaaltijd($mid, $mrid, $values['titel'], $values['aanmeld_limiet'], $values['datum'], $values['tijd'], $values['prijs'], $values['aanmeld_filter']);
			$this->content = new BeheerMaaltijdenView($maaltijd_aanmeldingen[0]);
			if ($maaltijd_aanmeldingen[1] > 0) {
				$this->content->setMelding($maaltijd_aanmeldingen[1] .' aanmelding'. ($maaltijd_aanmeldingen[1] !== 1 ? 'en' : '') .' verwijderd vanwege aanmeldrestrictie: '. $maaltijd_aanmeldingen[0]->getAanmeldFilter(), 2);
			}
		} else {
			$this->content = $form;
		}
	}
	
	public function action_verwijder($mid) {
		MaaltijdenModel::verwijderMaaltijd($mid);
		$this->content = new BeheerMaaltijdenView($mid);
	}
	
	public function action_herstel($mid) {
		$maaltijd = MaaltijdenModel::herstelMaaltijd($mid);
		$this->content = new BeheerMaaltijdenView($maaltijd->getMaaltijdId());
	}
	
	public function action_anderaanmelden($mid) {
		$form = new AanmeldingFormView($mid, true); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$aanmelding = AanmeldingenModel::aanmeldenVoorMaaltijd($mid, $values['voor_lid'], \LoginLid::instance()->getUid(), $values['aantal_gasten'], true);
			$this->content = new BeheerMaaltijdenView($aanmelding->getMaaltijd());
		} else {
			$this->content = $form;
		}
	}
	
	public function action_anderafmelden($mid) {
		$form = new AanmeldingFormView($mid, false); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijd = AanmeldingenModel::afmeldenDoorLid($mid, $values['voor_lid'], true);
			$this->content = new BeheerMaaltijdenView($maaltijd);
		} else {
			$this->content = $form;
		}
	}
	
	// Repetitie-Maaltijden ############################################################
	
	public function action_aanmaken($mrid) {
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		$form = new RepetitieMaaltijdenFormView($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijden = MaaltijdenModel::maakRepetitieMaaltijden($repetitie, strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (empty($maaltijden)) {
				throw new \Exception('Geen nieuwe maaltijden aangemaakt');
			}
			$this->content = new BeheerMaaltijdenView($maaltijden);
		} else {
			$this->content = $form;
		}
	}
}

?>