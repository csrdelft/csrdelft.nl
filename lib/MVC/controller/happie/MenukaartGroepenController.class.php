<?php

require_once 'MVC/model/happie/MenukaartGroepenModel.class.php';
require_once 'MVC/view/happie/MenukaartView.class.php';
require_once 'MVC/view/happie/forms/MenukaartForm.class.php';

/**
 * MenukaartGroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de Happietaria menukaart-groepen.
 * 
 */
class HappieMenukaartGroepenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, HappieMenukaartGroepenModel::instance());
		$this->acl = array(
			'overzicht'	 => 'groep:2014',
			'data'		 => 'groep:2014',
			'nieuw'		 => 'groep:2014',
			'wijzig'	 => 'groep:2014'
		);
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';
		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		parent::performAction($this->getParams(4));
	}

	public function overzicht() {
		$body = new HappieMenukaartGroepenView();
		$this->view = new CsrLayout3Page($body);
	}

	public function data() {
		$data = HappieMenukaartGroepenModel::instance()->find();
		$this->view = new DataTableResponse($data);
	}

	public function nieuw() {
		$groep = $this->model->newGroep();
		$form = new HappieMenukaartGroepForm($groep);
		if ($this->isPosted() AND $form->validate()) {
			$this->model->create($groep);
			setMelding('Menukaart-groep succesvol toegevoegd', 1);
			redirect(happieUrl);
		}
		$this->view = new CsrLayout3Page($form);
	}

	public function wijzig($id) {
		$groep = $this->model->getGroep((int) $id);
		if (!$groep) {
			setMelding('Menukaart-groep bestaat niet', -1);
			redirect(happieUrl);
		}
		$form = new HappieMenukaartGroepWijzigenForm($groep);
		if ($this->isPosted() AND $form->validate()) {
			$this->model->update($groep);
			setMelding('Wijziging succesvol opgeslagen', 1);
			redirect(happieUrl);
		}
		$this->view = new CsrLayout3Page($form);
	}

}
