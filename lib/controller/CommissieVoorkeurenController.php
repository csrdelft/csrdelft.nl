<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieCategorieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\commissievoorkeuren\AddCategorieFormulier;
use CsrDelft\view\commissievoorkeuren\AddCommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurPraesesOpmerkingForm;


/**
 * CommissieVoorkeurenController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissie voorkeuren.
 */
class CommissieVoorkeurenController extends AbstractController {
	/**
	 * @var CommissieVoorkeurModel
	 */
	private $commissieVoorkeurModel;
	/**
	 * @var VoorkeurCommissieModel
	 */
	private $voorkeurCommissieModel;
	/**
	 * @var VoorkeurCommissieCategorieModel
	 */
	private $voorkeurCommissieCategorieModel;
	/**
	 * @var VoorkeurOpmerkingModel
	 */
	private $voorkeurOpmerkingModel;

	public function __construct(
		CommissieVoorkeurModel $commissieVoorkeurModel,
		VoorkeurCommissieModel $voorkeurCommissieModel,
		VoorkeurCommissieCategorieModel $voorkeurCommissieCategorieModel,
		VoorkeurOpmerkingModel $voorkeurOpmerkingModel
	) {
		$this->commissieVoorkeurModel = $commissieVoorkeurModel;
		$this->voorkeurCommissieModel = $voorkeurCommissieModel;
		$this->voorkeurCommissieCategorieModel = $voorkeurCommissieCategorieModel;
		$this->voorkeurOpmerkingModel = $voorkeurOpmerkingModel;
	}

	public function overzicht() {
		return view('commissievoorkeuren.overzicht', [
			'categorien' => $this->voorkeurCommissieModel->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
		]);
	}

	public function commissie($commissieId) {
		/** @var VoorkeurCommissie $commissie */
		$commissie = $this->voorkeurCommissieModel->retrieveByUUID($commissieId);

		return view('commissievoorkeuren.commissie', [
			'voorkeuren' => $this->commissieVoorkeurModel->getVoorkeurenVoorCommissie($commissie),
			'commissie' => $commissie,
			'commissieFormulier' => new CommissieFormulier($commissie),
		]);
	}

	public function updatecommissie($commissieId) {
		$commissie = $this->voorkeurCommissieModel->retrieveByUUID($commissieId);
		$body = new CommissieFormulier($commissie);
		if ($body->validate()) {
			$this->voorkeurCommissieModel->update($commissie);
			setMelding('Aanpassingen commissie opgeslagen', 1);
		}
		return $this->redirectToRoute('commissievoorkeuren-updatecommissie');
	}

	public function nieuwecommissie() {
		$model = new VoorkeurCommissie();
		$form = new AddCommissieFormulier($model);

		if ($form->validate()) {
			$id = $this->voorkeurCommissieModel->create($model);
			return $this->redirectToRoute('commissievoorkeuren-commissie', ['commissieId' => $id]);
		}

		return view('commissievoorkeuren.overzicht', [
			'categorien' => $this->voorkeurCommissieModel->getByCategorie(),
			'commissieFormulier' => $form,
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
		]);
	}

	public function nieuwecategorie() {
		$model = new VoorkeurCommissieCategorie();
		$form = new AddCategorieFormulier($model);
		if ($form->validate()) {
			$this->voorkeurCommissieCategorieModel->create($model);
			return $this->redirectToRoute('commissievoorkeuren'); // Prevent resubmit
		}

		return view('commissievoorkeuren.overzicht', [
			'categorien' => $this->voorkeurCommissieModel->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => $form,
		]);
	}

	public function verwijdercategorie($categorieId) {
		/** @var VoorkeurCommissieCategorie $model */
		$model = $this->voorkeurCommissieCategorieModel->retrieveByUUID($categorieId);
		if (count($model->getCommissies()) == 0) {
			$this->voorkeurCommissieCategorieModel->delete($model);
			setMelding("Categorie '{$model->naam}' succesvol verwijderd", 1);

		} else {
			setMelding('Kan categorie niet verwijderen: is niet leeg', 2);
		}

		return $this->redirectToRoute('commissievoorkeuren');
	}

	public function lidpagina($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrToegangException();
		}

		$profiel = ProfielModel::get($uid);

		$voorkeuren = $this->commissieVoorkeurModel->getVoorkeurenVoorLid($profiel);
		$voorkeurenMap = [];
		$commissies = $this->voorkeurCommissieModel->find('zichtbaar = 1')->fetchAll();
		foreach ($commissies as $commissie) {
			$voorkeurenMap[$commissie->id] = null;
		}
		foreach ($voorkeuren as $voorkeur) {
			$voorkeurenMap[$voorkeur->cid] = $voorkeur;
		}

		$opmerking = $this->voorkeurOpmerkingModel->getOpmerkingVoorLid($profiel);

		return view('commissievoorkeuren.profiel', [
			'profiel' => $profiel,
			'voorkeuren' => $voorkeurenMap,
			'commissies' => $commissies,
			'lidOpmerking' => $opmerking->lidOpmerking,
			'opmerkingForm' => new CommissieVoorkeurPraesesOpmerkingForm($opmerking)
		]);
	}

	public function lidpaginaopmerking($uid) {
		$opmerking = $this->voorkeurOpmerkingModel->getOpmerkingVoorLid(ProfielModel::get($uid));
		$form = (new CommissieVoorkeurPraesesOpmerkingForm($opmerking));
		if ($form->validate()) {
			$this->voorkeurOpmerkingModel->updateOrCreate($opmerking);
		}

		return $this->redirectToRoute('commissievoorkeuren-lidpagina-lijst');
	}
}
