<?php

require_once 'MVC/model/GroepenModel.class.php';
require_once 'MVC/view/GroepenView.class.php';

/**
 * GroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van het groepen.
 */
class GroepenController extends Controller {

	public function __construct($query) {
		parent::__construct($query);
		try {
			$this->action = $this->getParam(2);
			$this->performAction($this->getParams(3));
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1);
			$this->action = 'commissies';
			$this->performAction(array());
		}
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		switch ($this->action) {
			case 'commissies':
			case 'besturen':
			case 'sjaarcies':
			case 'woonoorden':
			case 'werkgroepen':
			case 'onderverenigingen':
			case 'ketzers':
			case 'activiteiten':
			case 'conferenties':
				return !$this->isPosted();

			case 'aanmaken':
			case 'bewerken':
			case 'verwijderen':
			case 'aanmelden':
			case 'afmelden':
			case 'wijzigen':
				return $this->isPosted();

			default:
				$this->action = 'commissies';
				return true;
		}
	}

	/**
	 * Overzicht van commissies laten zien.
	 */
	public function commissies() {
		$body = new CommissiesView(CommissiesModel::instance()->find('status = ?', array(GroepStatus::HT)));
		if (LoginLid::mag('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
		} else {
			//uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addScript('groepen.js');
	}

}
