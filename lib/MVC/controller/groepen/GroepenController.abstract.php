<?php

require_once 'MVC/view/groepen/GroepenView.class.php';

/**
 * GroepenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor groepen.
 */
abstract class GroepenController extends Controller {

	public function __construct($query, GroepenModel $model) {
		parent::__construct($query, $model);
	}

	public function performAction(array $args = array()) {
		parent::performAction($this->getParams(3));
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		switch ($this->action) {
			case 'overzicht':
			case 'tonen':
				return !$this->isPosted();

			case 'aanmaken':
			case 'bewerken':
			case 'verwijderen':
			case 'aanmelden':
			case 'wijzigen':
			case 'afmelden':
				return $this->isPosted();

			default:
				$this->action = 'overzicht';
				return true;
		}
	}

	public function overzicht() {
		$groepen = $this->model->find('status = ?', array(GroepStatus::HT));
		$type = str_replace('Model', '', get_class($this->model));
		$this->view = new GroepenView($groepen, $type, $type . ' (h.t.)');
	}

	public function tonen($id) {
		//TODO
	}

	public function aanmaken() {
		//TODO
	}

	public function bewerken($id) {
		//TODO
	}

	public function verwijderen($id) {
		//TODO
	}

	public function aanmelden($id, $lid_id) {
		//TODO
	}

	public function wijzigen($id, $lid_id) {
		//TODO
	}

	public function afmelden($id, $lid_id) {
		//TODO
	}

}
