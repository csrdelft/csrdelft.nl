<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\maalcie\ArchiefMaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\GenericSuggestiesResponse;
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
use Symfony\Component\Routing\Annotation\Route;
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

	/**
	 * @return TemplateView
	 * @Route("/maaltijden/beheer/prullenbak", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function GET_prullenbak() {
		return view('maaltijden.pagina', [
			'titel' => 'Prullenbak maaltijdenbeheer',
			'content' => new PrullenbakMaaltijdenTable(),
		]);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/maaltijden/beheer/prullenbak", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function POST_prullenbak() {
		$data = $this->maaltijdenRepository->findByVerwijderd(true);

		return $this->tableData($data);
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse
	 * @Route("/maaltijden/beheer", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
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
	 * @param null $maaltijd_id
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/beheer/{maaltijd_id<\d*>}", methods={"GET"}, defaults={"maaltijd_id"=null})
	 * @Auth(P_MAAL_MOD)
	 */
	public function GET_beheer($maaltijd_id = null) {
		$modal = null;
		if ($maaltijd_id !== null) {
			$modal = $this->bewerk($maaltijd_id);
		}
		$repetities = $this->maaltijdRepetitiesRepository->findAll();
		return view('maaltijden.pagina', [
			'titel' => 'Maaltijdenbeheer',
			'content' => new BeheerMaaltijdenTable($repetities),
			'modal' => $modal,
		]);
	}

	/**
	 * @return TemplateView
	 * @Route("/maaltijden/beheer/archief", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function GET_archief() {
		return view('maaltijden.pagina', [
			'titel' => 'Archief maaltijdenbeheer',
			'content' => new ArchiefMaaltijdenTable(),
		]);
	}

	/**
	 * @param ArchiefMaaltijdenRepository $archiefMaaltijdenRepository
	 * @return GenericDataTableResponse
	 * @Route("/maaltijden/beheer/archief", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function POST_archief(ArchiefMaaltijdenRepository $archiefMaaltijdenRepository) {
		$data = $archiefMaaltijdenRepository->findAll();
		return $this->tableData($data);
	}

	/**
	 * @param int $maaltijd_id
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/beheer/toggle/{maaltijd_id}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function toggle($maaltijd_id) {
		$maaltijd = $this->maaltijdenRepository->getMaaltijd($maaltijd_id);

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
	 * @Route("/maaltijden/beheer/nieuw", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
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
		} elseif ($request->query->has('mlt_repetitie_id')) {
			$mlt_repetitie_id = $request->query->get('mlt_repetitie_id');
			$repetitie = $this->maaltijdRepetitiesRepository->getRepetitie($mlt_repetitie_id);
			$beginDatum = $repetitie->getFirstOccurrence();
			if ($repetitie->periode_in_dagen > 0) {
				return new RepetitieMaaltijdenForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
			} else {
				$maaltijd->repetitie = $repetitie;
				$maaltijd->product = $repetitie->product;
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
	 * @param Maaltijd|null $maaltijd
	 * @return GenericDataTableResponse|MaaltijdForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/beheer/bewerk/{maaltijd_id}", methods={"POST"}, defaults={"maaltijd_id"=null})
	 * @Auth(P_MAAL_MOD)
	 */
	public function bewerk(Maaltijd $maaltijd = null) {
		if (!$maaltijd) {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new ResourceNotFoundException();
			}
			$maaltijd = $this->maaltijdenRepository->retrieveByUuid($selection[0]);
		}

		$form = new MaaltijdForm($maaltijd, 'bewerk');
		if ($form->validate()) {
			$this->maaltijdenRepository->update($maaltijd);
			return $this->tableData([$maaltijd]);
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/beheer/verwijder", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function verwijder() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);

		$removed = new RemoveDataTableEntry($maaltijd->maaltijd_id, Maaltijd::class);

		if ($maaltijd->verwijderd) {
			$this->maaltijdenRepository->delete($maaltijd);
		} else {
			$maaltijd->verwijderd = true;
			$this->maaltijdenRepository->update($maaltijd);
		}

		return $this->tableData([$removed]);
	}

	/**
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/beheer/herstel", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function herstel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);

		$verwijderd = new RemoveDataTableEntry($maaltijd->maaltijd_id, Maaltijd::class);

		$maaltijd->verwijderd = false;
		$this->maaltijdenRepository->update($maaltijd);

		return $this->tableData([$verwijderd]);
	}

	/**
	 * @return GenericDataTableResponse|AanmeldingForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/beheer/aanmelden", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function aanmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenRepository->retrieveByUUID($selection[0]);
		$form = new AanmeldingForm($maaltijd, true); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$this->maaltijdAanmeldingenRepository->aanmeldenVoorMaaltijd($maaltijd, $values['voor_lid'], LoginService::getUid(), $values['aantal_gasten'], true);
			return $this->tableData([$maaltijd]);
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse|AanmeldingForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/maaltijden/beheer/afmelden", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
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
		return $this->redirectToRoute('csrdelft_maalcie_beheermaaltijden_get_prullenbak');
	}

	/**
	 * @return TemplateView
	 * @Route("/maaltijden/beheer/beoordelingen", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function GET_beoordelingen() {
		return view('maaltijden.maaltijd.maaltijd_beoordelingen', [
			'table' => new BeheerMaaltijdenBeoordelingenTable(),
		]);
	}

	/**
	 * @return BeheerMaaltijdenBeoordelingenLijst
	 * @Route("/maaltijden/beheer/beoordelingen", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function POST_beoordelingen() {
        $maaltijden = $this->maaltijdenRepository->getMaaltijdenHistorie();
        if (!LoginService::mag(P_MAAL_MOD)) {
        	// Als bekijker geen MaalCie-rechten heeft, toon alleen maaltijden waarvoor persoon sluitrechten had (kok)
					$maaltijden = array_filter($maaltijden, function ($maaltijd) {
						return $maaltijd->magSluiten(LoginService::getUid());
					});
				}
        return new BeheerMaaltijdenBeoordelingenLijst($maaltijden);
	}

	// Repetitie-Maaltijden ############################################################

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @return GenericDataTableResponse|RepetitieMaaltijdenForm
	 * @throws Throwable
	 * @Route("/maaltijden/beheer/aanmaken/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function aanmaken(MaaltijdRepetitie $repetitie) {
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

	/**
	 * @return TemplateView
	 * @Route("/maaltijden/beheer/onverwerkt", methods={"GET"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function onverwerkt() {
		return view('maaltijden.pagina', [
			'titel' => 'Onverwerkte Maaltijden',
			'content' => new OnverwerkteMaaltijdenTable(),
		]);
	}

	/**
	 * @param Request $request
	 * @return GenericSuggestiesResponse
	 * @Route("/maaltijden/beheer/suggesties", methods={"GET", "POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function suggesties(Request $request) {
		return new GenericSuggestiesResponse($this->maaltijdenRepository->getSuggesties($request->query->get('q')));
	}
}
