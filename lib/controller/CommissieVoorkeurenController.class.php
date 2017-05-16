<?php
namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\CommissieVoorkeurenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenProfiel;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenView;
use CsrDelft\view\CsrLayoutPage;


/**
 * CommissieVoorkeurenController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissie voorkeuren.
 */
class CommissieVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'overzicht'	 => 'bestuur',
				'lidpagina'	 => 'bestuur'
			);
		} else {
			$this->acl = array(
				'lidpagina' => 'bestuur'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function overzicht($commissieId = -1) {
		$body = new CommissieVoorkeurenView($commissieId);
		$this->view = new CsrLayoutPage($body);
	}

	public function lidpagina($uid = -1) {
		if (!ProfielModel::existsUid($uid)) {
			$this->exit_http(403);
		}
		if (isset($_POST['opmerkingen'])) {
			$voorkeur = new CommissieVoorkeurenModel($uid);
			$voorkeur->setPraesesOpmerking(filter_input(INPUT_POST, 'opmerkingen', FILTER_SANITIZE_STRING));
		}
		$body = new CommissieVoorkeurenProfiel($uid);
		$this->view = new CsrLayoutPage($body);
	}

}
