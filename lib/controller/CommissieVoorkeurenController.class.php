<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurenModel;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieCategorieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\model\ProfielModel;
use function CsrDelft\redirect;
use function CsrDelft\setMelding;
use CsrDelft\view\commissievoorkeuren\BeheerCommissieTable;
use CsrDelft\view\commissievoorkeuren\BeheerVoorkeurCommissieLijst;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenOverzicht;
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
                'overzicht' => 'bestuur',
                'nieuwecommissie' => 'bestuur',
                'nieuwecategorie' => 'bestuur'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';
        if (isset($_POST["nieuwecommissie"])) {
            $this->action = "nieuwecommissie";
        }
        if (isset($_POST["nieuwecategorie"])) {
            $this->action = "nieuwecategorie";
        }
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function overzicht($commissieId = null) {
	    $body = null;
	    if ($commissieId == null) {
	        $body = new CommissieVoorkeurenOverzicht();
        } else {
            $commissie = $commissieId != null ? VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId) : null;
            $body = new CommissieVoorkeurenView($commissie);
            if ($commissie != null && $body->validate()) {
                VoorkeurCommissieModel::instance()->update($commissie);
                setMelding('Aanpassingen commissie opgeslagen', 1);
            }
        }
		$this->view = new CsrLayoutPage($body);
	}

    public function nieuwecommissie() {
        $commissie = new VoorkeurCommissie();
        $commissie->naam = filter_input(INPUT_POST, 'commissienaam', FILTER_SANITIZE_STRING);
        $commissie->zichtbaar = false;
        $commissie->categorie_id = 1;
        $id = VoorkeurCommissieModel::instance()->create($commissie);
        redirect("/commissievoorkeuren/overzicht/".$id);
    }

    public function nieuwecategorie() {
        $cat = new VoorkeurCommissieCategorie();
        $cat->naam = filter_input(INPUT_POST, 'categorienaam', FILTER_SANITIZE_STRING);
        VoorkeurCommissieCategorieModel::instance()->create($cat);
        redirect("/commissievoorkeuren/");
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

}
