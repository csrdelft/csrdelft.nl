<?php

require_once 'model/CourantModel.class.php';
require_once 'view/courant/CourantView.class.php';
require_once 'view/courant/CourantBeheerView.class.php';

/**
 * CourantController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de courant.
 */
class CourantController extends AclController {

	public function __construct($query) {
		parent::__construct($query, new CourantModel());
		if (!$this->isPosted()) {
			$this->acl = array(
				'archief'		 => 'P_LEDEN_READ',
				'bekijken'		 => 'P_LEDEN_READ',
				'toevoegen'		 => 'P_MAIL_POST',
				'bewerken'		 => 'P_MAIL_POST',
				'verwijderen'	 => 'P_MAIL_POST',
				'verzenden'		 => 'P_MAIL_SEND'
			);
		} else {
			$this->acl = array(
				'toevoegen'	 => 'P_MAIL_POST',
				'bewerken'	 => 'P_MAIL_COMPOSE'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'toevoegen';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if ($this->hasParam(3)) {
			if ($this->action === 'archief' OR $this->action === 'bekijken') {
				$id = (int) $this->getParam(3);
			} else {
				$id = 0;
			}
			$success = $this->model->load((int) $id);
			if ($this->action === 'archief') {
				if ($success) {
					$this->action = 'bekijken';
				} else {
					$this->geentoegang();
				}
			}
		}
		parent::performAction($this->getParams(3));
	}

	public function archief() {
		require_once 'view/courant/CourantArchiefView.class.php';
		$body = new CourantArchiefView($this->model);
		$this->view = new CsrLayoutPage($body);
	}

	public function bekijken() {
		$this->view = new CourantView($this->model);
	}

	public function toevoegen() {
		if ($this->isPosted()) {
			if ($this->model->valideerBerichtInvoer()) {
				$success = $this->model->addBericht($_POST['titel'], $_POST['categorie'], $_POST['bericht']);
				if ($success) {
					setMelding('Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);
					if (isset($_SESSION['compose_snapshot'])) {
						$_SESSION['compose_snapshot'] = null;
					}
				} else {
					setMelding('Er ging iets mis met het invoeren van uw bericht. Probeer opnieuw, of stuur uw bericht in een mail naar <a href="mailto:pubcie@csrdelft.nl">pubcie@csrdelft.nl</a>.', -1);
				}
			} else {
				setMelding($this->model->getError(), -1);
			}
		}
		$body = new CourantBeheerView($this->model);
		$this->view = new CsrLayoutPage($body);
	}

	public function bewerken($iBerichtID) {
		$bericht = $this->model->getBericht($iBerichtID);
		if (!$bericht OR ! isset($bericht['uid']) OR ! $this->model->magBeheren($bericht['uid'])) {
			$this->geentoegang();
		}
		if ($this->isPosted()) {
			$success = $this->model->bewerkBericht($iBerichtID, $_POST['titel'], $_POST['categorie'], $_POST['bericht']);
			if ($success) {
				setMelding('Uw bewerkte bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);
				if (isset($_SESSION['compose_snapshot'])) {
					$_SESSION['compose_snapshot'] = null;
				}
			} else {
				setMelding('Er ging iets mis met het invoeren van uw bericht. Probeer opnieuw, of stuur uw bericht in een mail naar <a href="mailto:pubcie@csrdelft.nl">pubcie@csrdelft.nl</a>.', -1);
			}
		}
		$body = new CourantBeheerView($this->model);
		$body->edit($iBerichtID);
		$this->view = new CsrLayoutPage($body);
	}

	public function verwijderen($iBerichtID) {
		$bericht = $this->model->getBericht($iBerichtID);
		if (!$bericht OR ! isset($bericht['uid']) OR ! $this->model->magBeheren($bericht['uid'])) {
			$this->geentoegang();
		}
		if ($this->model->verwijderBericht($iBerichtID)) {
			setMelding('Uw bericht is verwijderd.', 1);
		} else {
			setMelding('Uw bericht is niet verwijderd.', -1);
		}
		$this->toevoegen();
	}

	public function verzenden($iedereen = null) {
		if ($this->model->getBerichtenCount() < 1) {
			setMelding('Lege courant kan niet worden verzonden', 0);
			redirect(CSR_ROOT . '/courant');
		}
		$courant = new CourantView($this->model);
		if ($iedereen === 'iedereen') {
			$courant->verzenden('csrmail@lists.knorrie.org');
			$this->model->leegCache();
			echo 'aan iedereen verzonden';
		} else {
			$courant->verzenden('pubcie@csrdelft.nl');
			echo '<a href="/courant/verzenden/iedereen">aan iedereen verzenden</a>';
		}
		exit;
	}

}
