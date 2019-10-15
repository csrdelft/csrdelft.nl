<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\maalcie\CorveeRepetitiesModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\view\maalcie\corvee\repetities\CorveeRepetitiesView;
use CsrDelft\view\maalcie\corvee\repetities\CorveeRepetitieView;
use CsrDelft\view\maalcie\forms\CorveeRepetitieForm;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class CorveeRepetitiesController {
	private $model;

	public function __construct() {
		$this->model = CorveeRepetitiesModel::instance();
	}

	public function beheer($crid = null, $mrid = null) {
		$modal = null;
		$maaltijdrepetitie = null;
		if (is_numeric($crid) && $crid > 0) {
			$modal = $this->bewerk($crid);
			$repetities = $this->model->getAlleRepetities();
		} elseif (is_numeric($mrid) && $mrid > 0) {
			$repetities = $this->model->getRepetitiesVoorMaaltijdRepetitie($mrid);
			$maaltijdrepetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
		} else {
			$repetities = $this->model->getAlleRepetities();
		}
		$view = new CorveeRepetitiesView($repetities, $maaltijdrepetitie);
		return view('default', ['content' => $view, 'modal' => $modal]);
	}

	public function maaltijd($mrid) {
		return $this->beheer(null, $mrid);
	}

	public function nieuw($mrid = null) {
		$repetitie = $this->model->nieuw(0, $mrid);
		return new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	public function bewerk($crid) {
		$repetitie = $this->model->getRepetitie($crid);
		return new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	public function opslaan($crid) {
		if ($crid > 0) {
			$view = $this->bewerk($crid);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			$values = $view->getValues();
			$mrid = empty($values['mlt_repetitie_id']) ? null : (int)$values['mlt_repetitie_id'];
			$voorkeurbaar = empty($values['voorkeurbaar']) ? false : (bool)$values['voorkeurbaar'];
			$repetitie_aantal = $this->model->saveRepetitie($crid, $mrid, $values['dag_vd_week'], $values['periode_in_dagen'], intval($values['functie_id']), $values['standaard_punten'], $values['standaard_aantal'], $voorkeurbaar);
			$maaltijdrepetitie = null;
			if (endsWith($_SERVER['HTTP_REFERER'], '/corvee/repetities/maaltijd/' . $mrid)) { // state of gui
				$maaltijdrepetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
			}
			if ($repetitie_aantal[1] > 0) {
				setMelding($repetitie_aantal[1] . ' voorkeur' . ($repetitie_aantal[1] !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
			}
			return new CorveeRepetitieView($repetitie_aantal[0], $maaltijdrepetitie);
		}

		return $view;
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
		$view = $this->opslaan($crid);
		if ($view instanceof CorveeRepetitieView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$aantal = CorveeTakenModel::instance()->updateRepetitieTaken($view->getModel(), $verplaats);
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

		return $view;
	}
}
