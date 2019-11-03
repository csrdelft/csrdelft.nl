<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\view\maalcie\forms\MaaltijdRepetitieForm;
use CsrDelft\view\maalcie\repetities\MaaltijdRepetitiesView;
use CsrDelft\view\maalcie\repetities\MaaltijdRepetitieView;

/**
 * MaaltijdRepetitiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaaltijdRepetitiesController {
	private $model;

	public function __construct() {
		$this->model = MaaltijdRepetitiesModel::instance();
	}

	public function beheer($mrid = null) {
		$modal = null;
		if (is_numeric($mrid) && $mrid > 0) {
			$modal = $this->bewerk($mrid);
		}
		$view = new MaaltijdRepetitiesView($this->model->getAlleRepetities());
		return view('default', ['content' => $view, [], $modal]);
	}

	public function nieuw() {
		return new MaaltijdRepetitieForm(new MaaltijdRepetitie()); // fetches POST values itself
	}

	public function bewerk($mrid) {
		return new MaaltijdRepetitieForm($this->model->getRepetitie($mrid)); // fetches POST values itself
	}

	public function opslaan($mrid) {
		if ($mrid > 0) {
			$view = $this->bewerk($mrid);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			$repetitie = $view->getModel();

			$aantal = $this->model->saveRepetitie($repetitie);
			if ($aantal > 0) {
				setMelding($aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
			}
			return new MaaltijdRepetitieView($repetitie);
		}

		return $view;
	}

	public function verwijder($mrid) {
		$aantal = MaaltijdRepetitiesModel::instance()->verwijderRepetitie($mrid);
		if ($aantal > 0) {
			setMelding($aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
		}
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		echo '<tr id="repetitie-row-' . $mrid . '" class="remove"></tr>';
		exit;
	}

	public function bijwerken($mrid) {
		$view = $this->opslaan($mrid);
		if ($view instanceof MaaltijdRepetitieView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$updated_aanmeldingen = MaaltijdenModel::instance()->updateRepetitieMaaltijden($view->getModel(), $verplaats);
			setMelding($updated_aanmeldingen[0] . ' maaltijd' . ($updated_aanmeldingen[0] !== 1 ? 'en' : '') . ' bijgewerkt' . ($verplaats ? ' en eventueel verplaatst.' : '.'), 1);
			if ($updated_aanmeldingen[1] > 0) {
				setMelding($updated_aanmeldingen[1] . ' aanmelding' . ($updated_aanmeldingen[1] !== 1 ? 'en' : '') . ' verwijderd vanwege aanmeldrestrictie: ' . $view->getModel()->abonnement_filter, 2);
			}
		}

		return $view;
	}

}
