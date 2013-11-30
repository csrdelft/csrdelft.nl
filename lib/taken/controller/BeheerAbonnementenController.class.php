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
				'beheer' => 'P_MAAL_MOD',
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
		$this->action = 'beheer';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$mrid = null;
		if ($this->hasParam(2)) {
			$mrid = intval($this->getParam(2));
		}
		$this->performAction($mrid);
	}
	
	public function action_beheer($alleenWaarschuwingen=true, $ingeschakeld=null) {
		$repetities = MaaltijdRepetitiesModel::getAlleRepetities();
		$matrix = AbonnementenModel::getAbonnementenMatrix($repetities, false, $alleenWaarschuwingen, $ingeschakeld);
		$this->content = new BeheerAbonnementenView($matrix, $repetities, $alleenWaarschuwingen, $ingeschakeld);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->content->addScript('taken.js');
	}
	
	public function action_ingeschakeld() {
		$this->action_beheer(false, true);
	}
	
	public function action_abonneerbaar() {
		$this->action_beheer(false, false);
	}
	
	public function action_voorlid() {
		$formField = new \LidField('voor_lid', null, null, 'allepersonen'); // fetches POST values itself
		if ($formField->valid()) {
			$uid = $formField->getValue();
			$matrix = array();
			$matrix[$uid] = AbonnementenModel::getAbonnementenVoorLid($uid, true);
			$this->content = new BeheerAbonnementenView($matrix);
		}
		else {
			$this->content = new BeheerAbonnementenView(array(), null);
			$this->content->setMelding($formField->error, -1);
		}
	}
	
	public function action_novieten() {
		$mrid = intval($_POST['mrid']);
		$aantal = AbonnementenModel::inschakelenAbonnementVoorNovieten($mrid);
		$matrix = AbonnementenModel::getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		$this->content = new BeheerAbonnementenView($matrix);
		$this->content->setMelding(
			$aantal .' abonnement'. ($aantal !== 1 ? 'en' : '') .' aangemaakt voor '.
			$novieten .' noviet'. ($novieten !== 1 ? 'en' : '') .'.', 1);
	}
	
	public function action_inschakelen($mrid) {
		$uid = $_POST['voor_lid'];
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$abo_aantal = AbonnementenModel::inschakelenAbonnement($mrid, $uid);
		$this->content = new BeheerAbonnementenView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$this->content->setMelding('Automatisch aangemeld voor '. $abo_aantal[1] .' maaltijd'. ($abo_aantal[1] === 1 ? '' : 'en'), 2);
		}
	}
	
	public function action_uitschakelen($mrid) {
		$uid = $_POST['voor_lid'];
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