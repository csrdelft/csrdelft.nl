<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\view\maalcie\forms\CorveeRepetitieForm;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class CorveeRepetitiesController {
	private $repetitie = null;
	/**
	 * @var CorveeRepetitiesRepository
	 */
	private $corveeRepetitiesRepository;
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;

	public function __construct(CorveeRepetitiesRepository $corveeRepetitiesRepository, MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository, CorveeTakenRepository $corveeTakenRepository) {
		$this->corveeRepetitiesRepository = $corveeRepetitiesRepository;
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
	}

	public function beheer($crid = null, $mrid = null) {
		$modal = null;
		$maaltijdrepetitie = null;
		if (is_numeric($crid) && $crid > 0) {
			$modal = $this->bewerk($crid);
			$repetities = $this->corveeRepetitiesRepository->getAlleRepetities();
		} elseif (is_numeric($mrid) && $mrid > 0) {
			$repetities = $this->corveeRepetitiesRepository->getRepetitiesVoorMaaltijdRepetitie($mrid);
			$maaltijdrepetitie = $this->maaltijdRepetitiesRepository->getRepetitie($mrid);
		} else {
			$repetities = $this->corveeRepetitiesRepository->getAlleRepetities();
		}
		return view('maaltijden.corveerepetitie.beheer_corvee_repetities', [
			'repetities' => $repetities,
			'maaltijdrepetitie' => $maaltijdrepetitie,
			'modal' => $modal,
		]);
	}

	public function maaltijd($mrid) {
		return $this->beheer(null, $mrid);
	}

	public function nieuw($mrid = null) {
		$repetitie = $this->corveeRepetitiesRepository->nieuw(0, $mrid);
		return new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	public function bewerk($crid) {
		$repetitie = $this->corveeRepetitiesRepository->getRepetitie($crid);
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
			list($repetitie, $aantal) = $this->corveeRepetitiesRepository->saveRepetitie($crid, $mrid, $values['dag_vd_week'], $values['periode_in_dagen'], intval($values['functie_id']), $values['standaard_punten'], $values['standaard_aantal'], $voorkeurbaar);
			if ($aantal > 0) {
				setMelding($aantal . ' voorkeur' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
			}

			$this->repetitie = $repetitie;
			return view('maaltijden.corveerepetitie.beheer_corvee_repetitie', ['repetitie' => $repetitie]);
		}

		return $view;
	}

	public function verwijder($crid) {
		$aantal = $this->corveeRepetitiesRepository->verwijderRepetitie($crid);
		if ($aantal > 0) {
			setMelding($aantal . ' voorkeur' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
		}
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		echo '<tr id="repetitie-row-' . $crid . '" class="remove"></tr>';
		exit;
	}

	public function bijwerken($crid) {
		$view = $this->opslaan($crid);
		if ($this->repetitie) { // Opslaan gelukt
			$verplaats = isset($_POST['verplaats_dag']);
			$aantal = $this->corveeTakenRepository->updateRepetitieTaken($this->repetitie, $verplaats);
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
