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
		if (!$this->isPosted()) {
			$this->view = new CsrLayoutPage($this->getContent());
			$this->view->addStyleSheet('groepen.css');
			$this->view->addScript('groepen.js');
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
		$this->view = new GroepenView(CommissiesModel::instance()->find('status = ?', array(GroepStatus::HT)), 'Commissies (h.t.)');
	}

	/**
	 * Overzicht van besturen laten zien.
	 */
	public function besturen() {
		$this->view = new GroepenView(BesturenModel::instance()->find(), 'Besturen');
	}

	/**
	 * Overzicht van sjaarcies laten zien.
	 */
	public function sjaarcies() {
		$this->view = new GroepenView(SjaarciesModel::instance()->find('status = ?', array(GroepStatus::HT)), 'SjaarCies (h.t.)');
	}

	/**
	 * Overzicht van woonoorden laten zien.
	 */
	public function woonoorden() {
		$this->view = new GroepenView(WoonoordenModel::instance()->find());
	}

	/**
	 * Overzicht van werkgroepen laten zien.
	 */
	public function werkgroepen() {
		$this->view = new GroepenView(WerkgroepenModel::instance()->find('status = ?', array(GroepStatus::HT)), 'Werkgroepen (h.t.)');
	}

	/**
	 * Overzicht van onderverenigingen laten zien.
	 */
	public function onderverenigingen() {
		$this->view = new GroepenView(OnderverenigingenModel::instance()->find(), 'Onderverenigingen');
	}

	/**
	 * Overzicht van ketzers laten zien.
	 */
	public function ketzers() {
		$this->view = new GroepenView(KetzersModel::instance()->find('status = ?', array(GroepStatus::HT)), 'Ketzers (h.t.)');
	}

	/**
	 * Overzicht van activiteiten laten zien.
	 */
	public function activiteiten() {
		$this->view = new GroepenView(ActiviteitenModel::instance()->find('status = ?', array(GroepStatus::HT)), 'Activiteiten (h.t.)');
	}

	/**
	 * Overzicht van conferenties laten zien.
	 */
	public function conferenties() {
		$this->view = new GroepenView(ConferentiesModel::instance()->find('status = ?', array(GroepStatus::HT)), 'Conferenties (h.t.)');
	}

}
