<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\forms\MaaltijdRepetitieForm;
use CsrDelft\view\maalcie\repetities\MaaltijdRepetitiesView;
use CsrDelft\view\maalcie\repetities\MaaltijdRepetitieView;

/**
 * MaaltijdRepetitiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property MaaltijdRepetitiesModel $model
 *
 */
class MaaltijdRepetitiesController extends AclController {

	public function __construct($query) {
		parent::__construct($query, MaaltijdRepetitiesModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_MAAL_MOD'
			);
		} else {
			$this->acl = array(
				'nieuw' => 'P_MAAL_MOD',
				'bewerk' => 'P_MAAL_MOD',
				'opslaan' => 'P_MAAL_MOD',
				'verwijder' => 'P_MAAL_MOD',
				'bijwerken' => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mrid = null;
		if ($this->hasParam(3)) {
			$mrid = (int)$this->getParam(3);
		}
		parent::performAction(array($mrid));
	}

	public function beheer($mrid = null) {
		$modal = null;
		if (is_int($mrid) && $mrid > 0) {
			$this->bewerk($mrid);
			$modal = $this->view;
		}
		$this->view = new MaaltijdRepetitiesView($this->model->getAlleRepetities());
		$this->view = new CsrLayoutPage($this->view);
		$this->view->modal = $modal;
	}

	public function nieuw() {
		$this->view = new MaaltijdRepetitieForm(new MaaltijdRepetitie()); // fetches POST values itself
	}

	public function bewerk($mrid) {
		$this->view = new MaaltijdRepetitieForm($this->model->getRepetitie($mrid)); // fetches POST values itself
	}

	public function opslaan($mrid) {
		if ($mrid > 0) {
			$this->bewerk($mrid);
		} else {
			$this->nieuw();
		}
		if ($this->view->validate()) {
			$repetitie = $this->view->getModel();

			$aantal = $this->model->saveRepetitie($repetitie);
			$this->view = new MaaltijdRepetitieView($repetitie);
			if ($aantal > 0) {
				setMelding($aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
			}
		}
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
		$this->opslaan($mrid);
		if ($this->view instanceof MaaltijdRepetitieView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$updated_aanmeldingen = MaaltijdenModel::instance()->updateRepetitieMaaltijden($this->view->getModel(), $verplaats);
			setMelding($updated_aanmeldingen[0] . ' maaltijd' . ($updated_aanmeldingen[0] !== 1 ? 'en' : '') . ' bijgewerkt' . ($verplaats ? ' en eventueel verplaatst.' : '.'), 1);
			if ($updated_aanmeldingen[1] > 0) {
				setMelding($updated_aanmeldingen[1] . ' aanmelding' . ($updated_aanmeldingen[1] !== 1 ? 'en' : '') . ' verwijderd vanwege aanmeldrestrictie: ' . $this->view->getModel()->abonnement_filter, 2);
			}
		}
	}

}
