<?php
namespace Taken\CRV;

require_once 'taken/model/FunctiesModel.class.php';
require_once 'taken/model/KwalificatiesModel.class.php';
require_once 'taken/view/BeheerFunctiesView.class.php';
require_once 'taken/view/forms/FunctieFormView.class.php';
require_once 'taken/view/forms/KwalificatieFormView.class.php';

/**
 * BeheerFunctiesController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerFunctiesController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		}
		else {
			$this->acl = array(
				'nieuw' => 'P_CORVEE_MOD',
				'bewerk' => 'P_CORVEE_MOD',
				'opslaan' => 'P_CORVEE_MOD',
				'verwijder' => 'P_CORVEE_MOD',
				'kwalificeer' => 'P_CORVEE_MOD',
				'dekwalificeer' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$fid = null;
		if ($this->hasParam(2)) {
			$fid = intval($this->getParam(2));
		}
		$this->performAction($fid);
	}
	
	public function action_beheer($fid=null) {
		if (is_int($fid) && $fid > 0) {
			$this->action_bewerk($fid);
		}
		$functies = FunctiesModel::getAlleFuncties();
		KwalificatiesModel::loadKwalificatiesVoorFuncties($functies);
		$this->content = new BeheerFunctiesView($functies, $this->getContent());
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->content->addScript('taken.js');
	}
	
	public function action_nieuw() {
		$functie = new CorveeFunctie();
		$this->content = new FunctieFormView($functie->getFunctieId(), $functie->getNaam(), $functie->getAfkorting(), $functie->getOmschrijving(), $functie->getEmailBericht(), $functie->getStandaardPunten(), $functie->getIsKwalificatieBenodigd()); // fetches POST values itself
	}
	
	public function action_bewerk($fid) {
		$functie = FunctiesModel::getFunctie($fid);
		$this->content = new FunctieFormView($functie->getFunctieId(), $functie->getNaam(), $functie->getAfkorting(), $functie->getOmschrijving(), $functie->getEmailBericht(), $functie->getStandaardPunten(), $functie->getIsKwalificatieBenodigd()); // fetches POST values itself
	}
	
	public function action_opslaan($fid) {
		$form = new FunctieFormView($fid); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$functie = FunctiesModel::saveFunctie($fid, $values['naam'], $values['afkorting'], $values['omschrijving'], $values['email_bericht'], $values['standaard_punten'], $values['kwalificatie_benodigd']);
			$functie->setGekwalificeerden(KwalificatiesModel::getKwalificatiesVoorFunctie($functie));
			$this->content = new BeheerFunctiesView($functie);
		}
		else {
			$this->content = $form;
		}
	}
	
	public function action_verwijder($fid) {
		FunctiesModel::verwijderFunctie($fid);
		$this->content = new BeheerFunctiesView($fid);
	}
	
	public function action_kwalificeer($fid) {
		$form = new KwalificatieFormView($fid); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			KwalificatiesModel::kwalificatieToewijzen($fid, $values['voor_lid']);
			$functie = FunctiesModel::getFunctie($fid);
			$functie->setGekwalificeerden(KwalificatiesModel::getKwalificatiesVoorFunctie($functie));
			$this->content = new BeheerFunctiesView($functie);
		}
		else {
			$this->content = $form;
		}
	}
	
	public function action_dekwalificeer($fid) {
		$uid = $_POST['voor_lid'];
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		KwalificatiesModel::kwalificatieTerugtrekken($fid, $uid);
		$functie = FunctiesModel::getFunctie($fid);
		$functie->setGekwalificeerden(KwalificatiesModel::getKwalificatiesVoorFunctie($functie));
		$this->content = new BeheerFunctiesView($functie);
	}
}

?>