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
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\commissievoorkeuren\AddCategorieFormulier;
use CsrDelft\view\commissievoorkeuren\AddCommissieFormulier;
use CsrDelft\view\commissievoorkeuren\BeheerCommissieTable;
use CsrDelft\view\commissievoorkeuren\BeheerVoorkeurCommissieLijst;
use CsrDelft\view\commissievoorkeuren\CommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenOverzicht;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenProfielView;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenView;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurPraesesOpmerkingForm;
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
				'nieuwecategorie' => 'bestuur',
				'verwijdercategorie' => 'bestuur'
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

	public function POST_overzicht($commissieId) {
		$commissie = VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId);
		$body = new CommissieFormulier($commissie);
		if ($body->validate()) {
			VoorkeurCommissieModel::instance()->update($commissie);
			setMelding('Aanpassingen commissie opgeslagen', 1);
		}
		redirect();

	}

	public function GET_overzicht($commissieId = null) {
		$body = null;
		if ($commissieId == null) {
			$body = new CommissieVoorkeurenOverzicht(VoorkeurCommissieModel::instance()->getByCategorie());
		} else {
			$commissie = VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId);
			$body = new CommissieVoorkeurenView($commissie);
		}
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('commissievoorkeuren');
	}

	public function nieuwecommissie() {
		$model = new VoorkeurCommissie();
		$form = new AddCommissieFormulier($model);
		if ($form->validate()) {
			$id = VoorkeurCommissieModel::instance()->create($model);
			redirect('/commissievoorkeuren/overzicht/' . $id);
		}
		$body = new CommissieVoorkeurenOverzicht(VoorkeurCommissieModel::instance()->getByCategorie(), $form, null);
		$this->view = new CsrLayoutPage($body);
	}

	public function nieuwecategorie() {
		$model = new VoorkeurCommissieCategorie();
		$form = new AddCategorieFormulier($model);
		if ($form->validate()) {
			VoorkeurCommissieCategorieModel::instance()->create($model);
			redirect('/commissievoorkeuren/'); // Prevent resubmit
		}
		$body = new CommissieVoorkeurenOverzicht(VoorkeurCommissieModel::instance()->getByCategorie(), null, $form);
		$this->view = new CsrLayoutPage($body);
	}

	public function verwijdercategorie($categorieId) {
		$model = VoorkeurCommissieCategorieModel::instance()->retrieveByUUID($categorieId);
		if (count($model->getCommissies()) == 0) {
			VoorkeurCommissieCategorieModel::instance()->delete($model);
			setMelding("Categorie '{$model->naam}' succesvol verwijderd", 1);

		} else {
			setMelding('Kan categorie niet verwijderen: is niet leeg', 2);
		}

		redirect("/commissievoorkeuren/");
	}

	public function GET_lidpagina($uid) {
		if (!ProfielModel::existsUid($uid)) {
			$this->exit_http(403);
		}
		$profiel = ProfielModel::get($uid);
		$body = new CommissieVoorkeurenProfielView($profiel);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('commissievoorkeuren');
	}

	public function POST_lidpagina($uid) {
		$opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid(ProfielModel::get($uid));
		$form = (new CommissieVoorkeurPraesesOpmerkingForm($opmerking));
		if ($form->validate()) {
			VoorkeurOpmerkingModel::instance()->updateOrCreate($opmerking);
			redirect();
		}
	}

}
