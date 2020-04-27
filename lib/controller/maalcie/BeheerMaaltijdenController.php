<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\maalcie\ArchiefMaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\maalcie\beheer\ArchiefMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenBeoordelingenLijst;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenBeoordelingenTable;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\OnverwerkteMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\PrullenbakMaaltijdenTable;
use CsrDelft\view\maalcie\forms\AanmeldingForm;
use CsrDelft\view\maalcie\forms\MaaltijdForm;
use CsrDelft\view\maalcie\forms\RepetitieMaaltijdenForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerMaaltijdenController extends AbstractController {
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(MaaltijdenRepository $maaltijdenRepository, MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository, MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	public function GET_prullenbak() {
		return view('maaltijden.pagina', [
			'titel' => 'Prullenbak maaltijdenbeheer',
			'content' => new PrullenbakMaaltijdenTable(),
		]);
	}

	public function POST_prullenbak() {
		$data = $this->maaltijdenRepository->findByVerwijderd(true);

		return $this->tableData($data);
	}

	public function POST_beheer(Request $request) {
		$filter = $request->query->get('filter', '');
		switch ($filter) {
			case 'prullenbak':
				$data = $this->maaltijdenRepository->findByVerwijderd(true);
				break;
			case 'onverwerkt':
				$data = $this->maaltijdenRepository->findBy(['verwijderd' => false, 'gesloten' => true, 'verwerkt' => false]);
				break;
			case 'alles':
				$data = $this->maaltijdenRepository->getMaaltijden();
				break;
			case 'toekomst':
			default:
				$data = $this->maaltijdenRepository->getMaaltijdenToekomst();
				break;
		}

		return $this->tableData($data);
	}

	/**
	 * @param null $mid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function GET_beheer($mid = null) {
		$modal = null;
		if ($mid !== null) {
			$modal = $this->bewerk($mid);
		}
		/** @var MaaltijdRepetitie[] $repetities */
		$repetities = $this->maaltijdRepetitiesRepository->findAll();
		return view('maaltijden.pagina', [
			'titel' => 'Maaltijdenbeheer',
			'content' => new BeheerMaaltijdenTable($repetities),
			'modal' => $modal,
		]);
	}

	public function GET_archief() {
		return view('maaltijden.pagina', [
			'titel' => 'Archief maaltijdenbeheer',
			'content' => new ArchiefMaaltijdenTable(),
		]);
	}

	public function POST_archief(ArchiefMaaltijdenRepository $archiefMaaltijdenRepository) {
		$data = $archiefMaaltijdenRepository->findAll();
		return $this->tableData($data);
	}

	/**
	 * @param int $mid
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function toggle($mid) {
		$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);

		if ($maaltijd->verwerkt) {
			throw new CsrGebruikerException('Maaltijd al verwerkt');
		}

		if ($maaltijd->gesloten) {
			$this->maaltijdenRepository->openMaaltijd($maaltijd);
		} else {
			$this->maaltijdenRepository->sluitMaaltijd($maaltijd);
		}

		return $this->tableData([$maaltijd]);
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|MaaltijdForm|RepetitieMaaltijdenForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function nieuw(Request $request) {
		$maaltijd = new Maaltijd();
		$form = new MaaltijdForm($maaltijd, 'nieuw');

		if ($form->validate()) {
			[$maaltijd, $aanmeldingen] = $this->maaltijdenRepository->saveMaaltijd($maaltijd);
			if ($aanmeldingen > 0) {
				setMelding($aanmeldingen . ' aanmelding' . ($aanmeldingen !== 1 ? 'en' : '') . ' verwijderd vanwege aanmeldrestrictie: ' . $maaltijd->aanmeld_filter, 2);
			}
			return $this->tableData([$maaltijd]);
		} elseif ($request->query->has('mrid')) {
			$mrid = $request->query->get('mrid');
			$repetitie = $this->maaltijdRepetitiesRepository->getRepetitie($mrid);
			$beginDatum = $repetitie->getFirstOccurrence();
			if ($repetitie->periode_in_dagen > 0) {
				return new RepetitieMaaltijdenForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
			} else {
				$maaltijd->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
				$maaltijd->product_id = $repetitie->product_id;
				$maaltijd->titel = $repetitie->standaard_titel;
				$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
				$maaltijd->tijd = $repetitie->standaard_tijd;
				$maaltijd->aanmeld_filter = $repetitie->abonnement_filter;
				return new MaaltijdForm($maaltijd, 'nieuw');
			}
		} else {
			return $form;
		}

	}

	/**
	 * @param int|null $mid
	 * @return GenericDataTableResponse|MaaltijdForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function bewerk($mid = null) {
		if ($mid === null) {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new ResourceNotFoundException();
			}
			$mid = $selection[0];
		}

		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($mid);
		$form = new MaaltijdForm($maaltijd, 'bewerk');
		if ($form->validate()) {
			$this->maaltijdenRepository->update($maaltijd);
			return $this->tableData([$maaltijd]);
		} else {
			return $form;
		}
	}

	/**
	 * @return RemoveRowsResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijder() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);

		if ($maaltijd->verwijderd) {
			$this->maaltijdenRepository->delete($maaltijd);
		} else {
			$maaltijd->verwijderd = true;
			$this->maaltijdenRepository->update($maaltijd);
		}

		return new RemoveRowsResponse(array($maaltijd));
	}

	/**
	 * @return RemoveRowsResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function herstel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);

		$maaltijd->verwijderd = false;
		$this->maaltijdenRepository->update($maaltijd);
		return new RemoveRowsResponse(array($maaltijd)); // Verwijder uit prullenbak
	}

	/**
	 * @return GenericDataTableResponse|AanmeldingForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);
		$form = new AanmeldingForm($maaltijd, true); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$this->maaltijdAanmeldingenRepository->aanmeldenVoorMaaltijd($maaltijd, $values['voor_lid'], LoginModel::getUid(), $values['aantal_gasten'], true);
			return $this->tableData([$maaltijd]);
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse|AanmeldingForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function afmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);
		$form = new AanmeldingForm($maaltijd, false); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$this->maaltijdAanmeldingenRepository->afmeldenDoorLid($maaltijd, $values['voor_lid'], true);
			return $this->tableData([$maaltijd]);
		} else {
			return $form;
		}
	}

	/**
	 * @return RedirectResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function leegmaken() {
		$aantal = $this->maaltijdenRepository->prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' maaltijd' : ' maaltijden') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		return $this->redirectToRoute('maalcie-beheer-maaltijden-prullenbak');
	}

	public function GET_beoordelingen() {
		return view('maaltijden.maaltijd.maaltijd_beoordelingen', [
			'table' => new BeheerMaaltijdenBeoordelingenTable(),
		]);
	}

	/**
	 * @return BeheerMaaltijdenBeoordelingenLijst
	 */
	public function POST_beoordelingen() {
        $maaltijden = $this->maaltijdenRepository->getMaaltijdenHistorie();
        if (!LoginModel::mag(P_MAAL_MOD)) {
        	// Als bekijker geen MaalCie-rechten heeft, toon alleen maaltijden waarvoor persoon sluitrechten had (kok)
					$maaltijden = array_filter($maaltijden, function ($maaltijd) {
						/** @var Maaltijd $maaltijd */
						return $maaltijd->magSluiten(LoginModel::getUid());
					});
				}
        return new BeheerMaaltijdenBeoordelingenLijst($maaltijden);
	}

	// Repetitie-Maaltijden ############################################################

	/**
	 * @param $mrid
	 * @return GenericDataTableResponse|RepetitieMaaltijdenForm
	 * @throws Throwable
	 */
	public function aanmaken($mrid) {
		$repetitie = $this->maaltijdRepetitiesRepository->getRepetitie($mrid);
		$form = new RepetitieMaaltijdenForm($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijden = $this->maaltijdenRepository->maakRepetitieMaaltijden($repetitie, $values['begindatum'], $values['einddatum']);
			if (empty($maaltijden)) {
				throw new CsrGebruikerException('Geen nieuwe maaltijden aangemaakt.');
			}
			return $this->tableData($maaltijden);
		} else {
			return $form;
		}
	}

	// Maalcie-fiscaat

	public function onverwerkt() {
		return view('maaltijden.pagina', [
			'titel' => 'Onverwerkte Maaltijden',
			'content' => new OnverwerkteMaaltijdenTable(),
		]);
	}
}
