<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\maalcie\KwalificatiesModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\functies\BeheerFunctiesView;
use CsrDelft\view\maalcie\corvee\functies\FunctieDeleteView;
use CsrDelft\view\maalcie\corvee\functies\FunctieForm;
use CsrDelft\view\maalcie\corvee\functies\FunctieView;
use CsrDelft\view\maalcie\corvee\functies\KwalificatieForm;

/**
 * BeheerFunctiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property FunctiesModel $model
 *
 */
class BeheerFunctiesController extends AclController {

	public function __construct($query) {
		parent::__construct($query, FunctiesModel::instance());
		if ($this->getMethod() == 'GET') {
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
		$fid = (int)$fid;
		$modal = null;
		if ($fid > 0) {
			$this->bewerken($fid);
			$modal = $this->view;
		}
		$functies = $this->model->getAlleFuncties(); // grouped by functie_id
		$this->view = new BeheerFunctiesView($functies);
		$this->view = new CsrLayoutPage($this->view, array(), $modal);
	}

	public function toevoegen() {
		$functie = $this->model->nieuw();
		$form = new FunctieForm($functie, $this->action); // fetches POST values itself
		if ($form->validate()) {
			$id = $this->model->create($functie);
			$functie->functie_id = (int)$id;
			setMelding('Toegevoegd', 1);
			$this->view = new FunctieView($functie);
		} else {
			$this->view = $form;
		}
	}

	public function bewerken($fid) {
		$functie = $this->model->get((int)$fid);
		$form = new FunctieForm($functie, $this->action); // fetches POST values itself
		if ($form->validate()) {
			$rowCount = $this->model->update($functie);
			if ($rowCount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			$this->view = new FunctieView($functie);
		} else {
			$this->view = $form;
		}
	}

	public function verwijderen($fid) {
		$functie = $this->model->get((int)$fid);
		$this->model->removeFunctie($functie);
		setMelding('Verwijderd', 1);
		$this->view = new FunctieDeleteView($fid);
	}

	public function kwalificeer($fid) {
		$functie = $this->model->get((int)$fid);
		$kwalificatie = KwalificatiesModel::instance()->nieuw($functie);
		$form = new KwalificatieForm($kwalificatie); // fetches POST values itself
		if ($form->validate()) {
			KwalificatiesModel::instance()->kwalificatieToewijzen($kwalificatie);
			$this->view = new FunctieView($functie);
		} else {
			$this->view = $form;
		}
	}

	public function dekwalificeer($fid, $uid) {
		$functie = $this->model->get((int)$fid);
		KwalificatiesModel::instance()->kwalificatieIntrekken($uid, $functie->functie_id);
		$this->view = new FunctieView($functie);
	}

}
