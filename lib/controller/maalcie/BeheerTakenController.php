<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\model\maalcie\CorveeHerinneringService;
use CsrDelft\model\maalcie\CorveeRepetitiesModel;
use CsrDelft\model\maalcie\CorveeToewijzenService;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\maalcie\forms\RepetitieCorveeForm;
use CsrDelft\view\maalcie\forms\TaakForm;
use CsrDelft\view\maalcie\forms\ToewijzenForm;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerTakenController extends AbstractController {
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var CorveeRepetitiesModel
	 */
	private $corveeRepetitiesModel;
	/**
	 * @var CorveeToewijzenService
	 */
	private $corveeToewijzenService;
	/**
	 * @var CorveeHerinneringService
	 */
	private $corveeHerinneringService;

	public function __construct(
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdenRepository $maaltijdenRepository,
		CorveeRepetitiesModel $corveeRepetitiesModel,
		CorveeToewijzenService $corveeToewijzenService,
		CorveeHerinneringService $corveeHerinneringService
	) {
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->corveeRepetitiesModel = $corveeRepetitiesModel;
		$this->corveeToewijzenService = $corveeToewijzenService;
		$this->corveeHerinneringService = $corveeHerinneringService;
	}

	public function maaltijd($mid) {
		return $this->beheer(null, $mid);
	}

	public function beheer($tid = null, $mid = null) {
		$modal = null;
		if (is_numeric($tid) && $tid > 0) {
			$modal = $this->bewerk($tid);
		}
		if (is_numeric($mid) && $mid > 0) {
			$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid, true);
			$taken = $this->corveeTakenRepository->getTakenVoorMaaltijd($mid, true);
		} else {
			$taken = $this->corveeTakenRepository->getAlleTaken();
			$maaltijd = null;
		}
		$model = [];
		if (isset($taken)) {
			foreach ($taken as $taak) {
				$datum = $taak->datum;
				if (!array_key_exists(date_format_intl($datum, DATE_FORMAT), $model)) {
					$model[date_format_intl($datum, DATE_FORMAT)] = array();
				}
				$model[date_format_intl($datum, DATE_FORMAT)][$taak->functie_id][] = $taak;
			}
		}
		return view('maaltijden.corveetaak.beheer_taken', [
			'taken' => $model,
			'maaltijd' => $maaltijd,
			'prullenbak' => false,
			'show' => $maaltijd !== null ? true : false,
			'repetities' => $this->corveeRepetitiesModel->getAlleRepetities(),
			'modal' => $modal,
		]);
	}

	public function bewerk($tid) {
		$taak = $this->corveeTakenRepository->getTaak($tid);
		return new TaakForm($taak, 'opslaan/' . $tid); // fetches POST values itself
	}

	public function prullenbak() {
		$taken = $this->corveeTakenRepository->getVerwijderdeTaken();
		$model = [];
		foreach ($taken as $taak) {
			$datum = $taak->datum;
			if (!array_key_exists(date_format_intl($datum, DATE_FORMAT), $model)) {
				$model[date_format_intl($datum, DATE_FORMAT)] = array();
			}
			$model[date_format_intl($datum, DATE_FORMAT)][$taak->functie_id][] = $taak;
		}
		return view('maaltijden.corveetaak.beheer_taken', [
			'taken' => $model,
			'maaltijd' => null,
			'repetities' => null,
			'prullenbak' => true,
			'show' => false,
		]);
	}

	public function herinneren() {
		$verstuurd_errors = $this->corveeHerinneringService->stuurHerinneringen();
		$verstuurd = $verstuurd_errors[0];
		$errors = $verstuurd_errors[1];
		$aantal = sizeof($verstuurd);
		$count = sizeof($errors);
		if ($count > 0) {
			setMelding($count . ' herinnering' . ($count !== 1 ? 'en' : '') . ' niet kunnen versturen!', -1);
			foreach ($errors as $error) {
				setMelding($error->getMessage(), 2); // toon wat fout is gegaan
			}
		}
		if ($aantal > 0) {
			setMelding($aantal . ' herinnering' . ($aantal !== 1 ? 'en' : '') . ' verstuurd!', 1);
			foreach ($verstuurd as $melding) {
				setMelding($melding, 1); // toon wat goed is gegaan
			}
		} else {
			setMelding('Geen herinneringen verstuurd.', 0);
		}
		return $this->redirectToRoute('corvee-beheer');
	}

	public function opslaan($tid) {
		if ($tid > 0) {
			$view = $this->bewerk($tid);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			/** @var CorveeTaak $values */
			$values = $view->getModel();
			$taak = $this->corveeTakenRepository->saveTaak((int)$tid, (int)$values->functie_id, $values->uid, $values->crv_repetitie_id, $values->maaltijd_id, $values->datum, $values->punten, $values->bonus_malus);
			$maaltijd = null;
			if (endsWith($_SERVER['HTTP_REFERER'], '/corvee/beheer/maaltijd/' . $values->maaltijd_id)) { // state of gui
				$maaltijd = $this->maaltijdenRepository->getMaaltijd($values->maaltijd_id);
			}
			return view('maaltijden.corveetaak.beheer_taak_lijst', [
				'taak' => $taak,
				'maaltijd' => $maaltijd,
				'show' => true,
				'prullenbak' => false,
			]);
		}

		return $view;
	}

	public function nieuw($mid = null) {
		$beginDatum = null;
		if ($mid !== null) {
			$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);
			$beginDatum = $maaltijd->datum;
		}
		$crid = filter_input(INPUT_POST, 'crv_repetitie_id', FILTER_SANITIZE_NUMBER_INT);
		if (!empty($crid)) {
			$repetitie = $this->corveeRepetitiesModel->getRepetitie((int)$crid);
			if ($mid === null) {
				$beginDatum = $this->corveeRepetitiesModel->getFirstOccurrence($repetitie);
				if ($repetitie->periode_in_dagen > 0) {
					return new RepetitieCorveeForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
				}
			}
			$taak = $this->corveeTakenRepository->vanRepetitie($repetitie, date_create_immutable($beginDatum), $mid);
			return new TaakForm($taak, 'opslaan/0'); // fetches POST values itself
		} else {
			$taak = new CorveeTaak();
			if (isset($beginDatum)) {
				$taak->datum = date_create_immutable($beginDatum);
			}
			$taak->maaltijd_id = $mid;
			return new TaakForm($taak, 'opslaan/0'); // fetches POST values itself
		}
	}

	public function verwijder($tid) {
		$this->corveeTakenRepository->verwijderTaak($tid);
		echo '<tr id="corveetaak-row-' . $tid . '" class="remove"></tr>';
		exit;
	}

	public function herstel($tid) {
		$this->corveeTakenRepository->herstelTaak($tid);
		echo '<tr id="corveetaak-row-' . $tid . '" class="remove"></tr>';
		exit;
	}

	public function toewijzen($tid) {
		$taak = $this->corveeTakenRepository->getTaak($tid);
		$uidField = new LidField('uid', null, null, 'leden'); // fetches POST values itself
		if ($uidField->validate()) {
			$taak = $this->corveeTakenRepository->getTaak($tid);
			$this->corveeTakenRepository->taakToewijzenAanLid($taak, $uidField->getValue());
			return view('maaltijden.corveetaak.beheer_taak_lijst', [
				'taak' => $taak,
				'maaltijd' => null,
				'show' => true,
				'prullenbak' => false,
			]);
		} else {
			$suggesties = $this->corveeToewijzenService->getSuggesties($taak);
			return new ToewijzenForm($taak, $suggesties); // fetches POST values itself
		}
	}

	public function puntentoekennen($tid) {
		$taak = $this->corveeTakenRepository->getTaak($tid);
		$this->corveeTakenRepository->puntenToekennen($taak);
		return view('maaltijden.corveetaak.beheer_taak_lijst', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	public function puntenintrekken($tid) {
		$taak = $this->corveeTakenRepository->getTaak($tid);
		$this->corveeTakenRepository->puntenIntrekken($taak);
		return view('maaltijden.corveetaak.beheer_taak_lijst', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	public function email($tid) {
		$taak = $this->corveeTakenRepository->getTaak($tid);
		$this->corveeHerinneringService->stuurHerinnering($taak);
		return view('maaltijden.corveetaak.beheer_taak_lijst', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	public function leegmaken() {
		$aantal = $this->corveeTakenRepository->prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' taak' : ' taken') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		return $this->redirectToRoute('corvee-beheer-prullenbak');
	}

	// Repetitie-Taken ############################################################

	public function aanmaken($crid) {
		$repetitie = $this->corveeRepetitiesModel->getRepetitie($crid);
		$form = new RepetitieCorveeForm($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$mid = (empty($values['maaltijd_id']) ? null : (int)$values['maaltijd_id']);
			$taken = $this->corveeTakenRepository->maakRepetitieTaken($repetitie, $values['begindatum'], $values['einddatum'], $mid);
			if (empty($taken)) {
				throw new CsrGebruikerException('Geen nieuwe taken aangemaakt.');
			}
			return view('maaltijden.corveetaak.beheer_taken_response', ['taken' => $taken]);
		} else {
			return $form;
		}
	}

}
