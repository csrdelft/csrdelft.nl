<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurenModel;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\model\ProfielModel;
use function CsrDelft\setMelding;
use CsrDelft\view\commissievoorkeuren\BeheerCommissieTable;
use CsrDelft\view\commissievoorkeuren\BeheerVoorkeurCommissieLijst;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenProfiel;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenView;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\formulier\datatable\DataTableResponse;



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
				'overzicht' => 'bestuur',
				'lidpagina' => 'bestuur',
                'beheer' => 'bestuur'
			);
		} else {
			$this->acl = array(
				'lidpagina' => 'bestuur',
                'beheer' => 'bestuur',
                'overzicht' => 'bestuur'
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

	public function overzicht($commissieId = null) {
	    $commissie = $commissieId != null ? VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId) : null;
		$form = new CommissieVoorkeurenView($commissie);
        if ($commissie != null && $form->validate()) {
            VoorkeurCommissieModel::instance()->update($commissie);
            setMelding('Aanpassingen commissie opgeslagen', 1);
        }
		$this->view = new CsrLayoutPage($form);
	}

	public function lidpagina($uid = -1) {
		if (!ProfielModel::existsUid($uid)) {
			$this->exit_http(403);
		}
		$profiel = ProfielModel::get($uid);
		if (isset($_POST['praeses-opmerking'])) {
			$opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid($profiel);
			VoorkeurOpmerkingModel::instance()->setPraesesOpmerking($opmerking, filter_input(INPUT_POST, 'praeses-opmerking', FILTER_SANITIZE_STRING));
		}

		$body = new CommissieVoorkeurenProfiel($profiel);
		$this->view = new CsrLayoutPage($body);
	}

	public function GET_beheer() {
        $body = new BeheerCommissieTable('Maaltijdenbeheer');
        $this->view = new CsrLayoutPage($body);
    }

    public function POST_beheer() {
        $filter = $this->hasParam('filter') ? $this->getParam('filter') : '';
        switch ($filter) {
            case 'prullenbak':
                $data = $this->model->find('verwijderd = true');
                break;
            case 'onverwerkt':
                $data = $this->model->find('verwijderd = false AND gesloten = true AND verwerkt = false');
                break;
            case 'alles':
                $data = $this->model->find();
                break;
            case 'toekomst':
            default:
                $data = VoorkeurCommissieModel::instance()->find();
                break;
        }

        $this->view = new BeheerVoorkeurCommissieLijst($data);
    }

}
