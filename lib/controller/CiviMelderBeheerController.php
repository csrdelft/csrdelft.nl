<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Deelnemer;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\repository\civimelder\DeelnemerRepository;
use CsrDelft\view\civimelder\ActiviteitAanmeldForm;
use CsrDelft\view\civimelder\ActiviteitForm;
use CsrDelft\view\civimelder\ActiviteitTabel;
use CsrDelft\view\civimelder\ReeksForm;
use CsrDelft\view\civimelder\ReeksTabel;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidObjectField;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\civimelder\ReeksRepository;

/**
 * @Route("/civimelder/beheer");
 */
class CiviMelderBeheerController extends AbstractController
{
	/**
	 * @var ReeksRepository
	 */
	private $reeksRepository;
	/**
	 * @var ActiviteitRepository
	 */
	private $activiteitRepository;
	/**
	 * @var DeelnemerRepository
	 */
	private $deelnemerRepository;

	public function __construct(ReeksRepository $reeksRepository,
															ActiviteitRepository $activiteitRepository,
															DeelnemerRepository $deelnemerRepository)
	{
		$this->reeksRepository = $reeksRepository;
		$this->activiteitRepository = $activiteitRepository;
		$this->deelnemerRepository = $deelnemerRepository;
	}

	/**
	 * @Route("", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function beheerTabel(): Response
	{
		return $this->render('default.html.twig', ['content' => new ReeksTabel()]);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function beheerTabelLijst(): GenericDataTableResponse
	{
		$reeksen = $this->reeksRepository->findAll();
		return $this->tableData($reeksen);
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Route("/reeks/nieuw", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function reeksNieuw(Request $request) {
		if (!Reeks::magAanmaken()) {
			throw new CsrGebruikerException('Mag geen reeks aanmaken');
		}

		$reeks = new Reeks();

		$form = $this->createFormulier(ReeksForm::class, $reeks, [
			'action' => $this->generateUrl('csrdelft_civimelderbeheer_reeksnieuw'),
			'nieuw' => true,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()->getManager()->persist($reeks);
			$this->getDoctrine()->getManager()->flush();

			return $this->tableData([$reeks]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Route("/reeks/bewerken", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function reeksBewerken(Request $request): Response
	{
		$selection = $this->getDataTableSelection();

		if ($selection) {
			$reeks = $this->reeksRepository->retrieveByUUID($selection[0]);
		} else {
			throw new CsrGebruikerException('Geen reeks geselecteerd');
		}

		if (!$reeks->magActiviteitenBeheren()) {
			throw new CsrGebruikerException('Mag reeks niet bewerken');
		}

		$form = $this->createFormulier(ReeksForm::class, $reeks, [
			'action' => $this->generateUrl('csrdelft_civimelderbeheer_reeksbewerken'),
			'nieuw' => false,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()->getManager()->persist($reeks);
			$this->getDoctrine()->getManager()->flush();

			return $this->tableData([$reeks]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/reeks/verwijderen", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function reeksVerwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		$reeks = $this->reeksRepository->retrieveByUUID($selection[0]);
		if (!$reeks || !Reeks::magAanmaken()) {
			throw new CsrGebruikerException('Mag reeks niet verwijderen');
		}

		$removed = new RemoveDataTableEntry($reeks->id, Reeks::class);

		$this->reeksRepository->delete($reeks);

		return $this->tableData([$removed]);
	}

	/**
	 * @Route("/activiteiten/{reeks}", methods={"GET"})
	 * @param Reeks $reeks
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	public function reeksDetail(Reeks $reeks): Response
	{
		$activiteitTabel = new ActiviteitTabel($reeks);
		return $activiteitTabel->toResponse();
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Route("/activiteiten/bewerken", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function activiteitBewerken(Request $request)
	{
		$selection = $this->getDataTableSelection();

		if ($selection) {
			$activiteit = $this->activiteitRepository->retrieveByUUID($selection[0]);
		} else {
			throw new CsrGebruikerException('Geen activiteit geselecteerd');
		}

		if (!$activiteit->getReeks()->magActiviteitenBeheren()) {
			throw new CsrGebruikerException('Mag activiteiten in reeks niet bewerken');
		}

		$form = $this->createFormulier(ActiviteitForm::class, $activiteit, [
			'action' => $this->generateUrl('csrdelft_civimelderbeheer_activiteitbewerken'),
			'nieuw' => false,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()->getManager()->persist($activiteit);
			$this->getDoctrine()->getManager()->flush();

			return $this->tableData([$activiteit]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/activiteiten/verwijderen", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function activiteitVerwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		$activiteit = $this->activiteitRepository->retrieveByUUID($selection[0]);
		if (!$activiteit || !$activiteit->getReeks()->magActiviteitenBeheren()) {
			throw new CsrGebruikerException('Mag activiteit niet verwijderen');
		}

		$removed = new RemoveDataTableEntry($activiteit->id, Activiteit::class);

		$this->activiteitRepository->delete($activiteit);

		return $this->tableData([$removed]);
	}

	/**
	 * @param Request $request
	 * @param Reeks $reeks
	 * @return GenericDataTableResponse|Response
	 * @Route("/activiteiten/nieuw/{reeks}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function activiteitNieuw(Request $request, Reeks $reeks) {
		if (!$reeks->magActiviteitenBeheren()) {
			throw new CsrGebruikerException('Mag geen activiteit in deze reeks aanmaken');
		}

		$activiteit = new Activiteit();
		$activiteit->setReeks($reeks);
		$activiteit->setGesloten(false);

		$form = $this->createFormulier(ActiviteitForm::class, $activiteit, [
			'action' => $this->generateUrl('csrdelft_civimelderbeheer_activiteitnieuw', ['reeks' => $reeks->getId()]),
			'nieuw' => true,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()->getManager()->persist($activiteit);
			$this->getDoctrine()->getManager()->flush();

			return $this->tableData([$activiteit]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @Route("/activiteiten/{reeks}", methods={"POST"})
	 * @param Reeks $reeks
	 * @param Request $request
	 * @return GenericDataTableResponse
	 * @Auth(P_LOGGED_IN)
	 */
	public function reeksDetailLijst(Reeks $reeks, Request $request): GenericDataTableResponse
	{
		if ($request->query->get('filter') === 'alles') {
			$activiteiten = $reeks->getActiviteiten();
		} else {
			$activiteiten = $reeks->getActiviteiten()->filter(function(Activiteit $activiteit) {
				return $activiteit->isInToekomst();
			})->getValues();
		}

		return $this->tableData($activiteiten);
	}

	/**
	 * @param Activiteit $activiteit
	 * @return Response
	 * @Route("/lijst/{activiteit}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijst(Activiteit $activiteit): Response
	{
		if (!$activiteit->magLijstBekijken()) {
			throw $this->createAccessDeniedException();
		}

		$deelnemers = $activiteit->getDeelnemers()->getValues();
		usort($deelnemers, function(Deelnemer $deelnemerA, Deelnemer $deelnemerB) {
			return $deelnemerA->getLid()->achternaam <=> $deelnemerB->getLid()->achternaam
				  ?: $deelnemerA->getLid()->voornaam <=> $deelnemerB->getLid()->voornaam;
		});

		$form = $this->createFormulier(ActiviteitAanmeldForm::class, $activiteit, [
			'action' => $this->generateUrl('csrdelft_civimelderbeheer_lijstaanmelden', ['activiteit' => $activiteit->getId()]),
		]);

		return $this->render('civimelder/deelnemers_lijst.html.twig', [
			'activiteit' => $activiteit,
			'deelnemers' => $deelnemers,
			'aanmeldForm' => $form->createView(),
		]);
	}

	/**
	 * @param Activiteit $activiteit
	 * @param bool $sluit
	 * @param ActiviteitRepository $activiteitRepository
	 * @return Response
	 * @Route("/lijst/{activiteit}/sluiten/{sluit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function sluit(Activiteit $activiteit, bool $sluit, ActiviteitRepository $activiteitRepository): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$activiteitRepository->sluit($activiteit, $sluit);
		return $this->render('civimelder/onderdelen/status.html.twig', ['activiteit' => $activiteit]);
	}

	/**
	 * @param Activiteit $activiteit
	 * @param Request $request
	 * @return Response
	 * @throws ORMException
	 * @Route("/lijst/{activiteit}/aanmelden", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijstAanmelden(Activiteit $activiteit, Request $request): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$form = $this->createFormulier(ActiviteitAanmeldForm::class, $activiteit, [
			'action' => $this->generateUrl('csrdelft_civimelderbeheer_lijstaanmelden', ['activiteit' => $activiteit->getId()]),
		]);
		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			/** @var RequiredLidObjectField $lidVeld */
			$lidVeld = $form->getField('lid');
			$lid = $lidVeld->getFormattedValue();

			/** @var RequiredIntField $aantalVeld */
			$aantalVeld = $form->getField('aantal');
			$aantal = $aantalVeld->getFormattedValue();

			$this->deelnemerRepository->aanmelden($activiteit, $lid, $aantal, true);
		}

		return $this->redirectToRoute('csrdelft_civimelderbeheer_lijst', ['activiteit' => $activiteit->getId()]);
	}

	/**
	 * @param Activiteit $activiteit
	 * @param Profiel $lid
	 * @return Response
	 * @throws ORMException
	 * @Route("/lijst/{activiteit}/afmelden/{lid}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijstAfmelden(Activiteit $activiteit, Profiel $lid): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$this->deelnemerRepository->afmelden($activiteit, $lid, true);
		return new Response("<div id='aanmelding-{$lid->uid}' class='remove'></div>");
	}

	/**
	 * @param Activiteit $activiteit
	 * @param Profiel $lid
	 * @param int $aantal
	 * @return Response
	 * @throws ORMException
	 * @Route("/lijst/{activiteit}/aantal/{lid}/{aantal}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijstAantal(Activiteit $activiteit, Profiel $lid, int $aantal): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$deelnemer = $this->deelnemerRepository->aantalAanpassen($activiteit, $lid, $aantal, true);
		return $this->render('civimelder/onderdelen/deelnemer.html.twig', [
			'activiteit' => $activiteit,
			'deelnemer' => $deelnemer,
			'naamweergave' => instelling('maaltijden', 'weergave_ledennamen_maaltijdlijst'),
		]);
	}
}
