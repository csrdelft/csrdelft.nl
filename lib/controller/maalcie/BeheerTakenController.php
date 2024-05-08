<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\corvee\CorveeHerinneringService;
use CsrDelft\service\corvee\CorveeToewijzenService;
use CsrDelft\view\formulier\invoervelden\LidObjectField;
use CsrDelft\view\maalcie\forms\RepetitieCorveeForm;
use CsrDelft\view\maalcie\forms\TaakForm;
use CsrDelft\view\maalcie\forms\ToewijzenForm;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;
use Twig\Environment;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerTakenController extends AbstractController
{
	/** @var CorveeTakenRepository */
	private $corveeTakenRepository;
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;
	/** @var CorveeRepetitiesRepository */
	private $corveeRepetitiesRepository;
	/** @var CorveeToewijzenService */
	private $corveeToewijzenService;
	/** @var CorveeHerinneringService */
	private $corveeHerinneringService;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager,
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdenRepository $maaltijdenRepository,
		CorveeRepetitiesRepository $corveeRepetitiesRepository,
		CorveeToewijzenService $corveeToewijzenService,
		CorveeHerinneringService $corveeHerinneringService
	) {
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->corveeRepetitiesRepository = $corveeRepetitiesRepository;
		$this->corveeToewijzenService = $corveeToewijzenService;
		$this->corveeHerinneringService = $corveeHerinneringService;
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return Response
	 * @Route("/corvee/beheer/maaltijd/{maaltijd_id}", methods={"GET"}, defaults={"maaltijd_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function maaltijd(Maaltijd $maaltijd)
	{
		return $this->beheer(null, $maaltijd);
	}

	/**
	 * @param CorveeTaak|null $taak
	 * @param Maaltijd|null $maaltijd
	 * @return Response
	 * @Route("/corvee/beheer/{taak_id<\d*>}/{maaltijd_id<\d*>}", methods={"GET"}, defaults={"taak_id"=null,"maaltijd_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function beheer(CorveeTaak $taak = null, Maaltijd $maaltijd = null): Response
	{
		$modal = null;
		if ($taak) {
			$modal = $this->bewerk($taak);
		}
		if ($maaltijd) {
			$taken = $this->corveeTakenRepository->getTakenVoorMaaltijd(
				$maaltijd->maaltijd_id,
				true
			);
		} else {
			$taken = $this->corveeTakenRepository->getAlleTaken();
			$maaltijd = null;
		}
		$model = [];
		if (isset($taken)) {
			foreach ($taken as $taak) {
				$datum = $taak->datum;
				if (
					!array_key_exists(
						DateUtil::dateFormatIntl($datum, DateUtil::DATE_FORMAT),
						$model
					)
				) {
					$model[DateUtil::dateFormatIntl($datum, DateUtil::DATE_FORMAT)] = [];
				}
				$model[DateUtil::dateFormatIntl($datum, DateUtil::DATE_FORMAT)][
					$taak->corveeFunctie->functie_id
				][] = $taak;
			}
		}
		return $this->render('maaltijden/corveetaak/beheer_taken.html.twig', [
			'taken' => $model,
			'maaltijd' => $maaltijd,
			'prullenbak' => false,
			'show' => $maaltijd !== null,
			'repetities' => $this->corveeRepetitiesRepository->getAlleRepetities(),
			'modal' => $modal,
		]);
	}

	/**
	 * @param CorveeTaak $taak
	 * @return TaakForm
	 * @Route("/corvee/beheer/bewerk/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function bewerk(CorveeTaak $taak): TaakForm
	{
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is verwijderd');
		}

		return new TaakForm($taak, 'opslaan/' . $taak->taak_id); // fetches POST values itself
	}

	/**
	 * @return Response
	 * @Route("/corvee/beheer/prullenbak", methods={"GET"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function prullenbak(): Response
	{
		$taken = $this->corveeTakenRepository->getVerwijderdeTaken();
		$model = [];
		foreach ($taken as $taak) {
			$datum = $taak->datum;
			if (
				!array_key_exists(
					DateUtil::dateFormatIntl($datum, DateUtil::DATE_FORMAT),
					$model
				)
			) {
				$model[DateUtil::dateFormatIntl($datum, DateUtil::DATE_FORMAT)] = [];
			}
			$model[DateUtil::dateFormatIntl($datum, DateUtil::DATE_FORMAT)][
				$taak->corveeFunctie->functie_id
			][] = $taak;
		}
		return $this->render('maaltijden/corveetaak/beheer_taken.html.twig', [
			'taken' => $model,
			'maaltijd' => null,
			'repetities' => null,
			'prullenbak' => true,
			'show' => false,
		]);
	}

	/**
	 * @return RedirectResponse
	 * @Route("/corvee/beheren/herinneren", methods={"GET"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function herinneren(): RedirectResponse
	{
		$verstuurd_errors = $this->corveeHerinneringService->stuurHerinneringen();
		$verstuurd = $verstuurd_errors[0];
		$errors = $verstuurd_errors[1];
		$aantal = sizeof($verstuurd);
		$count = sizeof($errors);
		if ($count > 0) {
			$this->addFlash(
				FlashType::ERROR,
				$count .
					' herinnering' .
					($count !== 1 ? 'en' : '') .
					' niet kunnen versturen!'
			);
			foreach ($errors as $error) {
				$this->addFlash(FlashType::WARNING, $error->getMessage()); // toon wat fout is gegaan
			}
		}
		if ($aantal > 0) {
			$this->addFlash(
				FlashType::SUCCESS,
				$aantal . ' herinnering' . ($aantal !== 1 ? 'en' : '') . ' verstuurd!'
			);
			foreach ($verstuurd as $melding) {
				$this->addFlash(FlashType::SUCCESS, $melding); // toon wat goed is gegaan
			}
		} else {
			$this->addFlash(FlashType::INFO, 'Geen herinneringen verstuurd.');
		}
		return $this->redirectToRoute('csrdelft_maalcie_beheertaken_beheer');
	}

	/**
	 * @param CorveeTaak|null $taak
	 * @return RepetitieCorveeForm|TaakForm|Response
	 * @throws Throwable
	 * @Route("/corvee/beheer/opslaan/{taak_id}", methods={"POST"}, defaults={"taak_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function opslaan(CorveeTaak $taak = null)
	{
		if ($taak) {
			$view = $this->bewerk($taak);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			/** @var CorveeTaak $values */
			$values = $view->getModel();
			$taak = $this->corveeTakenRepository->saveTaak($values);
			$maaltijd = null;
			if (
				str_ends_with(
					$_SERVER['HTTP_REFERER'],
					'/corvee/beheer/maaltijd/' .
						($taak->maaltijd ? $taak->maaltijd->maaltijd_id : '')
				)
			) {
				// state of gui
				$maaltijd = $taak->maaltijd;
			}
			return $this->render(
				'maaltijden/corveetaak/beheer_taak_lijst.html.twig',
				[
					'taak' => $taak,
					'maaltijd' => $maaltijd,
					'show' => true,
					'prullenbak' => false,
				]
			);
		}

		$this->entityManager->clear();

		return $view;
	}

	/**
	 * @param Maaltijd|null $maaltijd
	 * @return RepetitieCorveeForm|TaakForm
	 * @Route("/corvee/beheer/nieuw/{maaltijd_id}", methods={"POST"}, defaults={"maaltijd_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function nieuw(Maaltijd $maaltijd = null)
	{
		$beginDatum = null;
		if ($maaltijd) {
			$beginDatum = $maaltijd->datum;
		}
		$crv_repetitie_id = filter_input(
			INPUT_POST,
			'crv_repetitie_id',
			FILTER_SANITIZE_NUMBER_INT
		);
		if (!empty($crv_repetitie_id)) {
			$repetitie = $this->corveeRepetitiesRepository->getRepetitie(
				(int) $crv_repetitie_id
			);
			if (!$maaltijd) {
				$beginDatum = $this->corveeRepetitiesRepository->getFirstOccurrence(
					$repetitie
				);
				if ($repetitie->periode_in_dagen > 0) {
					return new RepetitieCorveeForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
				}
			}
			$taak = $this->corveeTakenRepository->vanRepetitie(
				$repetitie,
				date_create_immutable($beginDatum),
				$maaltijd
			);
			return new TaakForm($taak, 'opslaan'); // fetches POST values itself
		} else {
			$taak = new CorveeTaak();
			if ($beginDatum) {
				$taak->datum = $beginDatum;
			}
			$taak->maaltijd = $maaltijd;
			return new TaakForm($taak, 'opslaan'); // fetches POST values itself
		}
	}

	/**
	 * @param CorveeTaak $taak
	 * @Route("/corvee/beheer/verwijder/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function verwijder(CorveeTaak $taak)
	{
		if ($taak->verwijderd) {
			$this->entityManager->remove($taak);
		} else {
			$taak->verwijderd = true;
		}
		$this->entityManager->flush();

		echo '<tr id="corveetaak-row-' . $taak->taak_id . '" class="remove"></tr>';
		exit();
	}

	/**
	 * @param CorveeTaak $taak
	 * @Route("/corvee/beheer/herstel/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function herstel(CorveeTaak $taak)
	{
		if (!$taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is niet verwijderd');
		}
		$taakId = $taak->taak_id;
		$taak->verwijderd = false;
		$this->entityManager->flush();

		echo '<tr id="corveetaak-row-' . $taakId . '" class="remove"></tr>';
		exit();
	}

	/**
	 * @param Environment $twig
	 * @param CorveeTaak $taak
	 * @return ToewijzenForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/beheer/toewijzen/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function toewijzen(Environment $twig, CorveeTaak $taak)
	{
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is verwijderd');
		}

		$lidField = new LidObjectField('profiel', null, null, 'leden'); // fetches POST values itself
		if ($lidField->validate()) {
			$this->corveeTakenRepository->taakToewijzenAanLid(
				$taak,
				$taak->profiel,
				$lidField->getFormattedValue()
			);
			return $this->render(
				'maaltijden/corveetaak/beheer_taak_lijst.html.twig',
				[
					'taak' => $taak,
					'maaltijd' => null,
					'show' => true,
					'prullenbak' => false,
				]
			);
		} else {
			$suggesties = $this->corveeToewijzenService->getSuggesties($taak);
			return new ToewijzenForm($taak, $twig, $suggesties); // fetches POST values itself
		}
	}

	/**
	 * @param CorveeTaak $taak
	 * @return Response
	 * @Route("/corvee/beheer/puntentoekennen/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function puntentoekennen(CorveeTaak $taak): Response
	{
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is verwijderd');
		}

		$this->corveeTakenRepository->puntenToekennen($taak, $taak->profiel);

		$this->getDoctrine()
			->getManager()
			->flush();

		return $this->render('maaltijden/corveetaak/beheer_taak_lijst.html.twig', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	/**
	 * @param CorveeTaak $taak
	 * @return Response
	 * @Route("/corvee/beheer/puntenintrekken/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function puntenintrekken(CorveeTaak $taak): Response
	{
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is verwijderd');
		}

		$this->corveeTakenRepository->puntenIntrekken($taak, $taak->profiel);

		$this->getDoctrine()
			->getManager()
			->flush();

		return $this->render('maaltijden/corveetaak/beheer_taak_lijst.html.twig', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	/**
	 * @param CorveeTaak $taak
	 * @return Response
	 * @Route("/corvee/beheer/email/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function email(CorveeTaak $taak): Response
	{
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is verwijderd');
		}

		$this->corveeHerinneringService->stuurHerinnering($taak);

		return $this->render('maaltijden/corveetaak/beheer_taak_lijst.html.twig', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	/**
	 * @return RedirectResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/beheer/leegmaken", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function leegmaken(): RedirectResponse
	{
		$aantal = $this->corveeTakenRepository->prullenbakLeegmaken();
		$this->addFlash(
			$aantal == 0 ? FlashType::INFO : FlashType::SUCCESS,
			$aantal . ($aantal === 1 ? ' taak' : ' taken') . ' definitief verwijderd.'
		);
		return $this->redirectToRoute('csrdelft_maalcie_beheertaken_prullenbak');
	}

	// Repetitie-Taken ############################################################

	/**
	 * @param CorveeRepetitie $corveeRepetitie
	 * @return RepetitieCorveeForm|Response
	 * @throws Throwable
	 * @Route("/corvee/beheer/aanmaken/{crv_repetitie_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function aanmaken(CorveeRepetitie $corveeRepetitie)
	{
		$form = new RepetitieCorveeForm($corveeRepetitie); // fetches POST values itself

		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijd_id = empty($values['maaltijd_id'])
				? null
				: (int) $values['maaltijd_id'];
			$maaltijd = $maaltijd_id
				? $this->maaltijdenRepository->find($maaltijd_id)
				: null;
			$taken = $this->corveeTakenRepository->maakRepetitieTaken(
				$corveeRepetitie,
				$form->findByName('begindatum')->getFormattedValue(),
				$form->findByName('einddatum')->getFormattedValue(),
				$maaltijd
			);

			if (empty($taken)) {
				throw new CsrGebruikerException('Geen nieuwe taken aangemaakt.');
			}
			return $this->render(
				'maaltijden/corveetaak/beheer_taken_response.html.twig',
				['taken' => $taken]
			);
		} else {
			return $form;
		}
	}
}
