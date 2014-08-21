<?php

require_once 'MVC/model/maalcie/FunctiesModel.class.php';
require_once 'MVC/view/maalcie/BeheerFunctiesView.class.php';

/**
 * BeheerFunctiesController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerFunctiesController extends AclController {

	public function __construct($query) {
		parent::__construct($query, FunctiesModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'toevoegen' => 'P_CORVEE_MOD',
				'bewerken' => 'P_CORVEE_MOD',
				'verwijderen' => 'P_CORVEE_MOD',
				'kwalificeer' => 'P_CORVEE_MOD',
				'dekwalificeer' => 'P_CORVEE_MOD'
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

	public function beheer($fid = null) {
		$fid = (int) $fid;
		$popup = null;
		if ($fid > 0) {
			$this->bewerken($fid);
			$popup = $this->getView();
		}
		$functies = $this->model->getAlleFuncties(); // grouped by functie_id
		$this->view = new BeheerFunctiesView($functies);
		$this->view = new CsrLayoutPage($this->getView(), array(), $popup);
		$this->view->addStylesheet('/layout/css/taken.css');
		$this->view->addScript('/layout/js/taken.js');
	}

	public function toevoegen() {
		$functie = $this->model->newFunctie();
		$this->view = new FunctieForm($functie, $this->action); // fetches POST values itself
		if ($this->view->validate()) {
			$id = $this->model->create($functie);
			$functie->functie_id = (int) $id;
			setMelding('Toegevoegd', 1);
			$this->view = new FunctieView($functie);
		}
	}

	public function bewerken($fid) {
		$functie = $this->model->getFunctie((int) $fid);
		$this->view = new FunctieForm($functie, $this->action); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($functie);
			if ($rowcount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			$this->view = new FunctieView($functie);
		}
	}

	public function verwijderen($fid) {
		$functie = $this->model->getFunctie((int) $fid);
		$this->model->removeFunctie($functie);
		setMelding('Verwijderd', 1);
		$this->view = new FunctieDeleteView($fid);
	}

	public function kwalificeer($fid) {
		$functie = $this->model->getFunctie((int) $fid);
		$kwalificatie = KwalificatiesModel::instance()->newKwalificatie($functie);
		$this->view = new KwalificatieForm($kwalificatie); // fetches POST values itself
		if ($this->view->validate()) {
			KwalificatiesModel::instance()->kwalificatieToewijzen($kwalificatie);
			$this->view = new FunctieView($functie);
		}
	}

	public function dekwalificeer($fid, $uid) {
		$functie = $this->model->getFunctie((int) $fid);
		KwalificatiesModel::instance()->kwalificatieTerugtrekken($uid, $functie->functie_id);
		$this->view = new FunctieView($functie);
	}

}
