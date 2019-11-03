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
	public function overzicht() {
		return view('commissievoorkeuren.overzicht', [
			'categorien' => VoorkeurCommissieModel::instance()->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
		]);
	}

	public function commissie($commissieId) {
		/** @var VoorkeurCommissie $commissie */
		$commissie = VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId);

		return view('commissievoorkeuren.commissie', [
			'voorkeuren' => CommissieVoorkeurModel::instance()->getVoorkeurenVoorCommissie($commissie),
			'commissie' => $commissie,
			'commissieFormulier' => new CommissieFormulier($commissie),
		]);
	}

	public function updatecommissie($commissieId) {
		$commissie = VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId);
		$body = new CommissieFormulier($commissie);
		if ($body->validate()) {
			VoorkeurCommissieModel::instance()->update($commissie);
			setMelding('Aanpassingen commissie opgeslagen', 1);
		}
		return $this->redirectToRoute('commissievoorkeuren-updatecommissie');
	}

	public function nieuwecommissie() {
		$model = new VoorkeurCommissie();
		$form = new AddCommissieFormulier($model);

		if ($form->validate()) {
			$id = VoorkeurCommissieModel::instance()->create($model);
			return $this->redirectToRoute('commissievoorkeuren-commissie', ['commissieId' => $id]);
		}

		return view('commissievoorkeuren.overzicht', [
			'categorien' => VoorkeurCommissieModel::instance()->getByCategorie(),
			'commissieFormulier' => $form,
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
		]);
	}

	public function nieuwecategorie() {
		$model = new VoorkeurCommissieCategorie();
		$form = new AddCategorieFormulier($model);
		if ($form->validate()) {
			VoorkeurCommissieCategorieModel::instance()->create($model);
			return $this->redirectToRoute('commissievoorkeuren'); // Prevent resubmit
		}

		return view('commissievoorkeuren.overzicht', [
			'categorien' => VoorkeurCommissieModel::instance()->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => $form,
		]);
	}

	public function verwijdercategorie($categorieId) {
		/** @var VoorkeurCommissieCategorie $model */
		$model = VoorkeurCommissieCategorieModel::instance()->retrieveByUUID($categorieId);
		if (count($model->getCommissies()) == 0) {
			VoorkeurCommissieCategorieModel::instance()->delete($model);
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

		$voorkeuren = CommissieVoorkeurModel::instance()->getVoorkeurenVoorLid($profiel);
		$voorkeurenMap = [];
		$commissies = VoorkeurCommissieModel::instance()->find('zichtbaar = 1')->fetchAll();
		foreach ($commissies as $commissie) {
			$voorkeurenMap[$commissie->id] = null;
		}
		foreach ($voorkeuren as $voorkeur) {
			$voorkeurenMap[$voorkeur->cid] = $voorkeur;
		}

		$opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid($profiel);

		return view('commissievoorkeuren.profiel', [
			'profiel' => $profiel,
			'voorkeuren' => $voorkeurenMap,
			'commissies' => $commissies,
			'lidOpmerking' => $opmerking->lidOpmerking,
			'opmerkingForm' => new CommissieVoorkeurPraesesOpmerkingForm($opmerking)
		]);
	}

	public function lidpaginaopmerking($uid) {
		$opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid(ProfielModel::get($uid));
		$form = (new CommissieVoorkeurPraesesOpmerkingForm($opmerking));
		if ($form->validate()) {
			VoorkeurOpmerkingModel::instance()->updateOrCreate($opmerking);
		}

		return $this->redirectToRoute('commissievoorkeuren-lidpagina-lijst');
	}
}
