<?php

require_once 'taken/model/VoorkeurenModel.class.php';
require_once 'taken/view/BeheerVoorkeurenView.class.php';

/**
 * BeheerVoorkeurenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'inschakelen' => 'P_CORVEE_MOD',
				'uitschakelen' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction($this->getParams(3));
	}

	public function beheer() {
		$matrix_repetities = VoorkeurenModel::getVoorkeurenMatrix();
		$this->view = new BeheerVoorkeurenView($matrix_repetities[0], $matrix_repetities[1]);
		$this->view = new CsrLayoutPage($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}

	public function inschakelen($crid, $uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$voorkeur = VoorkeurenModel::inschakelenVoorkeur((int) $crid, $uid);
		$voorkeur->setVanLid($voorkeur->getLidId());
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid, $uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		VoorkeurenModel::uitschakelenVoorkeur((int) $crid, $uid);
		$voorkeur = new CorveeVoorkeur((int) $crid, null);
		$voorkeur->setVanLid($uid);
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

}
