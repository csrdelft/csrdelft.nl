<?php
namespace Taken\MLT;

require_once 'taken/model/AbonnementenModel.class.php';
require_once 'taken/model/MaaltijdRepetitiesModel.class.php';
require_once 'taken/view/BeheerAbonnementenView.class.php';

/**
 * BeheerMaaltijdenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerAbonnementenController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'waarschuwingen' => 'P_MAAL_MOD',
				'ingeschakeld' => 'P_MAAL_MOD',
				'abonneerbaar' => 'P_MAAL_MOD'
			);
		}
		else {
			$this->acl = array(
				'inschakelen' => 'P_MAAL_MOD',
				'uitschakelen' => 'P_MAAL_MOD',
				'voorlid' => 'P_MAAL_MOD',
				'novieten' => 'P_MAAL_MOD'
			);
		}
		$this->action = 'waarschuwingen';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mrid = null;
		if ($this->hasParam(3)) {
			$mrid = intval($this->getParam(3));
		}
		$this->performAction(array($mrid));
	}
	
	private function beheer($alleenWaarschuwingen, $ingeschakeld=null) {
		$repetities = MaaltijdRepetitiesModel::getAlleRepetities();
		$matrix = AbonnementenModel::getAbonnementenMatrix($repetities, false, $alleenWaarschuwingen, $ingeschakeld);
		$this->content = new BeheerAbonnementenView($matrix, $repetities, $alleenWaarschuwingen, $ingeschakeld);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->content->addScript('taken.js');
	}
	
	public function waarschuwingen() {
		$this->beheer(true, null);
	}
	
	public function ingeschakeld() {
		$this->beheer(false, true);
	}
	
	public function abonneerbaar() {
		$this->beheer(false, false);
	}
	
	public function voorlid() {
		$formField = new \LidField('voor_lid', null, null, 'allepersonen'); // fetches POST values itself
		if ($formField->valid()) {
			$uid = $formField->getValue();
			$matrix = array();
			$matrix[$uid] = AbonnementenModel::getAbonnementenVoorLid($uid, false, true);
			$this->content = new BeheerAbonnementenView($matrix);
		}
		else {
			$this->content = new BeheerAbonnementenView(array(), null);
			$this->content->setMelding($formField->error, -1);
		}
	}
	
	public function novieten() {
		$mrid = (int) filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
		$aantal = AbonnementenModel::inschakelenAbonnementVoorNovieten($mrid);
		$matrix = AbonnementenModel::getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		$this->content = new BeheerAbonnementenView($matrix);
		$this->content->setMelding(
			$aantal .' abonnement'. ($aantal !== 1 ? 'en' : '') .' aangemaakt voor '.
			$novieten .' noviet'. ($novieten !== 1 ? 'en' : '') .'.', 1);
	}
	
	public function inschakelen($mrid) {
		$uid = filter_input(INPUT_POST, 'voor_lid', FILTER_SANITIZE_STRING);
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$abo_aantal = AbonnementenModel::inschakelenAbonnement($mrid, $uid);
		$this->content = new BeheerAbonnementenView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$this->content->setMelding('Automatisch aangemeld voor '. $abo_aantal[1] .' maaltijd'. ($abo_aantal[1] === 1 ? '' : 'en'), 2);
		}
	}
	
	public function uitschakelen($mrid) {
		$uid = filter_input(INPUT_POST, 'voor_lid', FILTER_SANITIZE_STRING);
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$abo_aantal = AbonnementenModel::uitschakelenAbonnement($mrid, $uid);
		$this->content = new BeheerAbonnementenView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$this->content->setMelding('Automatisch afgemeld voor '. $abo_aantal[1] .' maaltijd'. ($abo_aantal[1] === 1 ? '' : 'en'), 2);
		}
	}
}

?>