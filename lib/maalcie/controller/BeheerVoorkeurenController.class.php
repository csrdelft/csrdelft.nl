<?php

require_once 'maalcie/model/CorveeVoorkeurenModel.class.php';
require_once 'maalcie/view/BeheerVoorkeurenView.class.php';

/**
 * BeheerVoorkeurenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'inschakelen'	 => 'P_CORVEE_MOD',
				'uitschakelen'	 => 'P_CORVEE_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function beheer() {
		$matrix_repetities = CorveeVoorkeurenModel::getVoorkeurenMatrix();
		$this->view = new BeheerVoorkeurenView($matrix_repetities[0], $matrix_repetities[1]);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet($this->view->getCompressedStyleUrl('layout', 'maalcie'), true);
		$this->view->addScript($this->view->getCompressedScriptUrl('layout', 'maalcie'), true);
	}

	public function inschakelen($crid, $uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$voorkeur = CorveeVoorkeurenModel::inschakelenVoorkeur((int) $crid, $uid);
		$voorkeur->setVanUid($voorkeur->getUid());
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid, $uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		CorveeVoorkeurenModel::uitschakelenVoorkeur((int) $crid, $uid);
		$voorkeur = new CorveeVoorkeur((int) $crid, null);
		$voorkeur->setVanUid($uid);
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

}
