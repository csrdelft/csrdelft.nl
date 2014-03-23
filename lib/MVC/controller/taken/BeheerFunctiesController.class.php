<?php

require_once 'MVC/model/taken/FunctiesModel.class.php';
require_once 'MVC/view/taken/BeheerFunctiesView.class.php';

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
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction($this->getParams(3));
	}

	public function beheer($fid = null) {
		$fid = (int) $fid;
		$popup = null;
		if ($fid > 0) {
			$this->bewerken($fid);
			$popup = $this->getContent();
		}
		$functies = $this->model->getAlleFuncties(true); // grouped by functie_id
		$this->view = new BeheerFunctiesView($functies);
		$zijkolom = array(new BlockMenuView(MenuModel::instance()->getMenuTree('Corveebeheer')));
		$this->view = new CsrLayoutPage($this->getContent(), $zijkolom, $popup);
		$this->view->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->view->addScript('taken.js');
	}

	public function toevoegen() {
		$functie = $this->model->newFunctie();
		$this->view = new FunctieFormView($functie, $this->action); // fetches POST values itself
		if ($this->view->validate()) {
			$id = $this->model->create($functie);
			$functie->functie_id = (int) $id;
			setMelding('Toegevoegd', 1);
			$this->view = new FunctieView($functie);
		}
	}

	public function bewerken($fid) {
		$functie = $this->model->getFunctie((int) $fid);
		$this->view = new FunctieFormView($functie, $this->action); // fetches POST values itself
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
		$this->model->removeFunctie((int) $fid);
		setMelding('Verwijderd', 1);
		$this->view = new FunctieDeleteView($fid);
	}

	public function kwalificeer($fid) {
		$functie = $this->model->getFunctie($fid);
		$kwalificatie = KwalificatiesModel::instance()->newKwalificatie($functie);
		$this->view = new KwalificatieFormView($kwalificatie); // fetches POST values itself
		if ($this->view->validate()) {
			KwalificatiesModel::instance()->kwalificatieToewijzen($kwalificatie);
			$this->view = new FunctieView($functie);
		}
	}

	public function dekwalificeer($fid) {
		$uid = filter_input(INPUT_POST, 'voor_lid', FILTER_SANITIZE_STRING);
		$functie = $this->model->getFunctie($fid);
		KwalificatiesModel::instance()->kwalificatieTerugtrekken($uid, $functie->functie_id);
		$this->view = new FunctieView($functie);
	}

}
