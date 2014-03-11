<?php

require_once 'MVC/model/taken/FunctiesModel.class.php';
require_once 'taken/model/KwalificatiesModel.class.php';
require_once 'MVC/view/taken/BeheerFunctiesView.class.php';
require_once 'taken/view/forms/KwalificatieFormView.class.php';

/**
 * BeheerFunctiesController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerFunctiesController extends AclController {

	/**
	 * Data access model
	 * @var FunctiesModel
	 */
	private $model;

	public function __construct($query) {
		parent::__construct($query);
		$this->model = new FunctiesModel();
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
		$functies = $this->model->getAlleFuncties();
		KwalificatiesModel::loadKwalificatiesVoorFuncties($functies);
		$this->view = new BeheerFunctiesView($functies);
		$menu = new MenuModel();
		$zijkolom = array(new BlockMenuView($menu->getMenuTree('Corveebeheer')));
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
			$functie->gekwalificeerden = KwalificatiesModel::getKwalificatiesVoorFunctie($functie);
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
			if (!$functie->kwalificatie_benodigd) { // niet (meer) voorkeurbaar
				$rowcount = KwalificatiesModel::verwijderKwalificaties((int) $fid);
				if ($rowcount > 0) {
					setMelding($rowcount . ' kwalificaties verwijderd', 1);
				}
			}
			$functie->gekwalificeerden = KwalificatiesModel::getKwalificatiesVoorFunctie($functie);
			$this->view = new FunctieView($functie);
		}
	}

	public function verwijderen($fid) {
		try {
			Database::instance()->beginTransaction();
			$this->model->removeFunctie((int) $fid);
			setMelding('Verwijderd', 1);
			$this->view = new FunctieDeleteView($fid);
			Database::instance()->commit();
		} catch (Exception $e) {
			Database::instance()->rollback();
			throw $e; // rethrow to controller
		}
	}

	public function kwalificeer($fid) {
		$form = new KwalificatieFormView($fid); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			KwalificatiesModel::kwalificatieToewijzen($fid, $values['voor_lid']);
			$functie = $this->model->getFunctie($fid);
			$functie->gekwalificeerden = KwalificatiesModel::getKwalificatiesVoorFunctie($functie);
			$this->view = new BeheerFunctiesView($functie);
		} else {
			$this->view = $form;
		}
	}

	public function dekwalificeer($fid) {
		$uid = filter_input(INPUT_POST, 'voor_lid', FILTER_SANITIZE_STRING);
		if (!\Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		KwalificatiesModel::kwalificatieTerugtrekken($fid, $uid);
		$functie = $this->model->getFunctie($fid);
		$functie->gekwalificeerden = KwalificatiesModel::getKwalificatiesVoorFunctie($functie);
		$this->view = new BeheerFunctiesView($functie);
	}

}
