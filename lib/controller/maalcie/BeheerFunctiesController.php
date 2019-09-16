<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\maalcie\KwalificatiesModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\functies\BeheerFunctiesView;
use CsrDelft\view\maalcie\corvee\functies\FunctieDeleteView;
use CsrDelft\view\maalcie\corvee\functies\FunctieForm;
use CsrDelft\view\maalcie\corvee\functies\FunctieView;
use CsrDelft\view\maalcie\corvee\functies\KwalificatieForm;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerFunctiesController {
	private $model;

	public function __construct() {
		$this->model = FunctiesModel::instance();
	}

	public function beheer($fid = null) {
		$fid = (int)$fid;
		$modal = null;
		if ($fid > 0) {
			$modal = $this->bewerken($fid);
		}
		$functies = $this->model->getAlleFuncties(); // grouped by functie_id
		$view = new BeheerFunctiesView($functies);
		return new CsrLayoutPage($view, array(), $modal);
	}

	public function toevoegen() {
		$functie = $this->model->nieuw();
		$form = new FunctieForm($functie, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			$id = $this->model->create($functie);
			$functie->functie_id = (int)$id;
			setMelding('Toegevoegd', 1);
			return new FunctieView($functie);
		} else {
			return $form;
		}
	}

	public function bewerken($fid) {
		$functie = $this->model->get((int)$fid);
		$form = new FunctieForm($functie, 'bewerken'); // fetches POST values itself
		if ($form->validate()) {
			$rowCount = $this->model->update($functie);
			if ($rowCount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			return new FunctieView($functie);
		} else {
			return $form;
		}
	}

	public function verwijderen($fid) {
		$functie = $this->model->get((int)$fid);
		$this->model->removeFunctie($functie);
		setMelding('Verwijderd', 1);
		return new FunctieDeleteView($fid);
	}

	public function kwalificeer($fid) {
		$functie = $this->model->get((int)$fid);
		$kwalificatie = KwalificatiesModel::instance()->nieuw($functie);
		$form = new KwalificatieForm($kwalificatie); // fetches POST values itself
		if ($form->validate()) {
			KwalificatiesModel::instance()->kwalificatieToewijzen($kwalificatie);
			return new FunctieView($functie);
		} else {
			return $form;
		}
	}

	public function dekwalificeer($fid, $uid) {
		$functie = $this->model->get((int)$fid);
		KwalificatiesModel::instance()->kwalificatieIntrekken($uid, $functie->functie_id);
		return new FunctieView($functie);
	}

}
