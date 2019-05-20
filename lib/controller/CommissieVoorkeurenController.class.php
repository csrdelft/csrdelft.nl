<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieCategorieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\commissievoorkeuren\AddCategorieFormulier;
use CsrDelft\view\commissievoorkeuren\AddCommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenOverzicht;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenProfielView;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenView;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurPraesesOpmerkingForm;


/**
 * CommissieVoorkeurenController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissie voorkeuren.
 */
class CommissieVoorkeurenController {
	public function overzicht() {
		return view('default', [
			'content' => new CommissieVoorkeurenOverzicht(VoorkeurCommissieModel::instance()->getByCategorie())
		]);
	}

	public function commissie($commissieId) {
		return view('default', [
			'content' => new CommissieVoorkeurenView(VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId)),
		]);
	}

	public function updatecommissie($commissieId) {
		$commissie = VoorkeurCommissieModel::instance()->retrieveByUUID($commissieId);
		$body = new CommissieFormulier($commissie);
		if ($body->validate()) {
			VoorkeurCommissieModel::instance()->update($commissie);
			setMelding('Aanpassingen commissie opgeslagen', 1);
		}
		redirect();
	}

	public function nieuwecommissie() {
		$model = new VoorkeurCommissie();
		$form = new AddCommissieFormulier($model);

		if ($form->validate()) {
			$id = VoorkeurCommissieModel::instance()->create($model);
			redirect('/commissievoorkeuren/overzicht/' . $id);
		}

		return view('default', [
			'content' => new CommissieVoorkeurenOverzicht(VoorkeurCommissieModel::instance()->getByCategorie(), $form, null),
		]);
	}

	public function nieuwecategorie() {
		$model = new VoorkeurCommissieCategorie();
		$form = new AddCategorieFormulier($model);
		if ($form->validate()) {
			VoorkeurCommissieCategorieModel::instance()->create($model);
			redirect('/commissievoorkeuren'); // Prevent resubmit
		}
		return view('default', [
			'content' => new CommissieVoorkeurenOverzicht(VoorkeurCommissieModel::instance()->getByCategorie(), null, $form),
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

		redirect("/commissievoorkeuren");
	}

	public function lidpagina($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrToegangException();
		}

		return view('default', [
			'content' => new CommissieVoorkeurenProfielView(ProfielModel::get($uid)),
		]);
	}

	public function lidpaginaopmerking($uid) {
		$opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid(ProfielModel::get($uid));
		$form = (new CommissieVoorkeurPraesesOpmerkingForm($opmerking));
		if ($form->validate()) {
			VoorkeurOpmerkingModel::instance()->updateOrCreate($opmerking);
			redirect();
		}
	}
}
