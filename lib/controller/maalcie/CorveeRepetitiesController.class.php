<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\maalcie\CorveeRepetitiesModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\repetities\CorveeRepetitiesView;
use CsrDelft\view\maalcie\corvee\repetities\CorveeRepetitieView;
use CsrDelft\view\maalcie\forms\CorveeRepetitieForm;

/**
 * CorveeRepetitiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property CorveeRepetitiesModel $model
 *
 */
class CorveeRepetitiesController extends AclController {

	public function __construct($query) {
		parent::__construct($query, CorveeRepetitiesModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD',
				'maaltijd' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'nieuw' => 'P_CORVEE_MOD',
				'bewerk' => 'P_CORVEE_MOD',
				'opslaan' => 'P_CORVEE_MOD',
				'verwijder' => 'P_CORVEE_MOD',
				'bijwerken' => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$crid = null;
		if ($this->hasParam(3)) {
			$crid = (int)$this->getParam(3);
		}
		parent::performAction(array($crid));
	}

	public function beheer($crid = null, $mrid = null) {
		$modal = null;
		$maaltijdrepetitie = null;
		if (is_int($crid) && $crid > 0) {
			$this->bewerk($crid);
			$modal = $this->view;
			$repetities = $this->model->getAlleRepetities();
		} elseif (is_int($mrid) && $mrid > 0) {
			$repetities = $this->model->getRepetitiesVoorMaaltijdRepetitie($mrid);
			$maaltijdrepetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
		} else {
			$repetities = $this->model->getAlleRepetities();
		}
		$this->view = new CorveeRepetitiesView($repetities, $maaltijdrepetitie);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
		$this->view->modal = $modal;
	}

	public function maaltijd($mrid) {
		$this->beheer(null, $mrid);
	}

	public function nieuw($mrid = null) {
		$repetitie = $this->model->nieuw(0, $mrid);
		$this->view = new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	public function bewerk($crid) {
		$repetitie = $this->model->getRepetitie($crid);
		$this->view = new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	public function opslaan($crid) {
		if ($crid > 0) {
			$this->bewerk($crid);
		} else {
			$this->nieuw();
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$mrid = empty($values['mlt_repetitie_id']) ? null : (int)$values['mlt_repetitie_id'];
			$voorkeurbaar = empty($values['voorkeurbaar']) ? false : (bool)$values['voorkeurbaar'];
			$repetitie_aantal = $this->model->saveRepetitie($crid, $mrid, $values['dag_vd_week'], $values['periode_in_dagen'], intval($values['functie_id']), $values['standaard_punten'], $values['standaard_aantal'], $voorkeurbaar);
			$maaltijdrepetitie = null;
			if (endsWith($_SERVER['HTTP_REFERER'], maalcieUrl . '/maaltijd/' . $mrid)) { // state of gui
				$maaltijdrepetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
			}
			$this->view = new CorveeRepetitieView($repetitie_aantal[0], $maaltijdrepetitie);
			if ($repetitie_aantal[1] > 0) {
				setMelding($repetitie_aantal[1] . ' voorkeur' . ($repetitie_aantal[1] !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
			}
		}
	}

	public function verwijder($crid) {
		$aantal = $this->model->verwijderRepetitie($crid);
		if ($aantal > 0) {
			setMelding($aantal . ' voorkeur' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
		}
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		echo '<tr id="repetitie-row-' . $crid . '" class="remove"></tr>';
		exit;
	}

	public function bijwerken($crid) {
		$this->opslaan($crid);
		if ($this->view instanceof CorveeRepetitieView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$aantal = CorveeTakenModel::instance()->updateRepetitieTaken($this->view->getModel(), $verplaats);
			if ($aantal['update'] < $aantal['day']) {
				$aantal['update'] = $aantal['day'];
			}
			setMelding(
				$aantal['update'] . ' corveeta' . ($aantal['update'] !== 1 ? 'ken' : 'ak') . ' bijgewerkt waarvan ' .
				$aantal['day'] . ' van dag verschoven.', 1);
			$aantal['datum'] += $aantal['maaltijd'];
			setMelding(
				$aantal['datum'] . ' corveeta' . ($aantal['datum'] !== 1 ? 'ken' : 'ak') . ' aangemaakt waarvan ' .
				$aantal['maaltijd'] . ' maaltijdcorvee.', 1);
		}
	}

}
