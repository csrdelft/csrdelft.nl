<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\entity\aanmelder\Deelnemer;
use CsrDelft\entity\aanmelder\Reeks;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\aanmelder\AanmeldActiviteitRepository;
use CsrDelft\repository\aanmelder\DeelnemerRepository;
use CsrDelft\repository\aanmelder\ReeksRepository;
use CsrDelft\view\aanmelder\AanmeldActiviteitAanmeldForm;
use CsrDelft\view\aanmelder\AanmeldActiviteitForm;
use CsrDelft\view\aanmelder\AanmeldActiviteitTabel;
use CsrDelft\view\aanmelder\ReeksForm;
use CsrDelft\view\aanmelder\ReeksTabel;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidObjectField;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // ;

#[Route(path: '/aanmelder/beheer')]
class AanmelderBeheerController extends AbstractController
{
	public function __construct(
		private readonly ReeksRepository $reeksRepository,
		private readonly AanmeldActiviteitRepository $activiteitRepository,
		private readonly DeelnemerRepository $deelnemerRepository
	) {
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '', methods: ['GET'])]
	public function beheerTabel(): Response
	{
		return $this->render('default.html.twig', ['content' => new ReeksTabel()]);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '', methods: ['POST'])]
	public function beheerTabelLijst(): GenericDataTableResponse
	{
		$reeksen = $this->reeksRepository->findAll();
		return $this->tableData($reeksen);
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/reeks/nieuw', methods: ['POST'])]
	public function reeksNieuw(Request $request)
	{
		if (!Reeks::magAanmaken()) {
			throw new CsrGebruikerException('Mag geen reeks aanmaken');
		}

		$reeks = new Reeks();

		$form = $this->createFormulier(ReeksForm::class, $reeks, [
			'action' => $this->generateUrl('csrdelft_aanmelderbeheer_reeksnieuw'),
			'nieuw' => true,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->persist($reeks);
			$this->getDoctrine()
				->getManager()
				->flush();

			return $this->tableData([$reeks]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/reeks/bewerken', methods: ['POST'])]
	public function reeksBewerken(Request $request)
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
			'action' => $this->generateUrl('csrdelft_aanmelderbeheer_reeksbewerken'),
			'nieuw' => false,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->persist($reeks);
			$this->getDoctrine()
				->getManager()
				->flush();

			return $this->tableData([$reeks]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @return GenericDataTableResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/reeks/verwijderen', methods: ['GET', 'POST'])]
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
	 * @param Reeks $reeks
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/activiteiten/{reeks}', methods: ['GET'])]
	public function reeksDetail(Reeks $reeks): Response
	{
		$activiteitTabel = new AanmeldActiviteitTabel($reeks);
		return $activiteitTabel->toResponse();
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/activiteiten/bewerken', methods: ['POST'])]
	public function activiteitBewerken(Request $request)
	{
		$selection = $this->getDataTableSelection();

		if ($selection) {
			$activiteit = $this->activiteitRepository->retrieveByUUID($selection[0]);
		} else {
			throw new CsrGebruikerException('Geen activiteit geselecteerd');
		}

		if (!$activiteit->getReeks()->magActiviteitenBeheren()) {
			throw new CsrGebruikerException(
				'Mag activiteiten in reeks niet bewerken'
			);
		}

		$form = $this->createFormulier(AanmeldActiviteitForm::class, $activiteit, [
			'action' => $this->generateUrl(
				'csrdelft_aanmelderbeheer_activiteitbewerken'
			),
			'nieuw' => false,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->persist($activiteit);
			$this->getDoctrine()
				->getManager()
				->flush();

			return $this->tableData([$activiteit]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @return GenericDataTableResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/activiteiten/verwijderen', methods: ['GET', 'POST'])]
	public function activiteitVerwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		$activiteit = $this->activiteitRepository->retrieveByUUID($selection[0]);
		if (!$activiteit || !$activiteit->getReeks()->magActiviteitenBeheren()) {
			throw new CsrGebruikerException('Mag activiteit niet verwijderen');
		}

		$removed = new RemoveDataTableEntry(
			$activiteit->id,
			AanmeldActiviteit::class
		);

		$this->activiteitRepository->delete($activiteit);

		return $this->tableData([$removed]);
	}

	/**
	 * @param Request $request
	 * @param Reeks $reeks
	 * @return GenericDataTableResponse|Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/activiteiten/nieuw/{reeks}', methods: ['POST'])]
	public function activiteitNieuw(Request $request, Reeks $reeks)
	{
		if (!$reeks->magActiviteitenBeheren()) {
			throw new CsrGebruikerException(
				'Mag geen activiteit in deze reeks aanmaken'
			);
		}

		$activiteit = new AanmeldActiviteit();
		$activiteit->setReeks($reeks);
		$activiteit->setGesloten(false);

		$form = $this->createFormulier(AanmeldActiviteitForm::class, $activiteit, [
			'action' => $this->generateUrl(
				'csrdelft_aanmelderbeheer_activiteitnieuw',
				['reeks' => $reeks->getId()]
			),
			'nieuw' => true,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->persist($activiteit);
			$this->getDoctrine()
				->getManager()
				->flush();

			return $this->tableData([$activiteit]);
		}

		return new Response($form->createModalView());
	}

	/**
	 * @param Reeks $reeks
	 * @param Request $request
	 * @return GenericDataTableResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/activiteiten/{reeks}', methods: ['POST'])]
	public function reeksDetailLijst(
		Reeks $reeks,
		Request $request
	): GenericDataTableResponse {
		if ($request->query->get('filter') === 'alles') {
			$activiteiten = $reeks->getActiviteiten();
		} else {
			$activiteiten = $reeks
				->getActiviteiten()
				->filter(
					fn(AanmeldActiviteit $activiteit) => $activiteit->isInToekomst()
				)
				->getValues();
		}

		return $this->tableData($activiteiten);
	}

	/**
	 * @param AanmeldActiviteit $activiteit
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/lijst/{activiteit}', methods: ['GET'])]
	public function lijst(AanmeldActiviteit $activiteit): Response
	{
		if (!$activiteit->magLijstBekijken()) {
			throw $this->createAccessDeniedException();
		}

		$deelnemers = $activiteit->getDeelnemers()->getValues();
		usort(
			$deelnemers,
			fn(Deelnemer $deelnemerA, Deelnemer $deelnemerB) => $deelnemerA->getLid()
				->achternaam <=> $deelnemerB->getLid()->achternaam ?:
			$deelnemerA->getLid()->voornaam <=> $deelnemerB->getLid()->voornaam
		);

		$form = $this->createFormulier(
			AanmeldActiviteitAanmeldForm::class,
			$activiteit,
			[
				'action' => $this->generateUrl(
					'csrdelft_aanmelderbeheer_lijstaanmelden',
					['activiteit' => $activiteit->getId()]
				),
			]
		);

		return $this->render('aanmelder/deelnemers_lijst.html.twig', [
			'activiteit' => $activiteit,
			'deelnemers' => $deelnemers,
			'aanmeldForm' => $form->createView(),
		]);
	}

	/**
	 * @param AanmeldActiviteit $activiteit
	 * @param bool $sluit
	 * @param AanmeldActiviteitRepository $activiteitRepository
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/lijst/{activiteit}/sluiten/{sluit}', methods: ['POST'])]
	public function sluit(
		AanmeldActiviteit $activiteit,
		bool $sluit,
		AanmeldActiviteitRepository $activiteitRepository
	): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$activiteitRepository->sluit($activiteit, $sluit);
		return $this->render('aanmelder/onderdelen/status.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
	 * @param AanmeldActiviteit $activiteit
	 * @param Request $request
	 * @return Response
	 * @throws ORMException
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/lijst/{activiteit}/aanmelden', methods: ['POST'])]
	public function lijstAanmelden(
		AanmeldActiviteit $activiteit,
		Request $request
	): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$form = $this->createFormulier(
			AanmeldActiviteitAanmeldForm::class,
			$activiteit,
			[
				'action' => $this->generateUrl(
					'csrdelft_aanmelderbeheer_lijstaanmelden',
					['activiteit' => $activiteit->getId()]
				),
			]
		);
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

		return $this->redirectToRoute('csrdelft_aanmelderbeheer_lijst', [
			'activiteit' => $activiteit->getId(),
		]);
	}

	/**
	 * @param AanmeldActiviteit $activiteit
	 * @param Profiel $lid
	 * @return Response
	 * @throws ORMException
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/lijst/{activiteit}/afmelden/{lid}', methods: ['POST'])]
	public function lijstAfmelden(
		AanmeldActiviteit $activiteit,
		Profiel $lid
	): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$this->deelnemerRepository->afmelden($activiteit, $lid, true);
		return new Response(
			"<div id='aanmelding-{$lid->uid}' class='remove'></div>"
		);
	}

	/**
	 * @param AanmeldActiviteit $activiteit
	 * @param Profiel $lid
	 * @param int $aantal
	 * @return Response
	 * @throws ORMException
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/lijst/{activiteit}/aantal/{lid}/{aantal}', methods: ['POST'])]
	public function lijstAantal(
		AanmeldActiviteit $activiteit,
		Profiel $lid,
		int $aantal
	): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$deelnemer = $this->deelnemerRepository->aantalAanpassen(
			$activiteit,
			$lid,
			$aantal,
			true
		);
		return $this->render('aanmelder/onderdelen/deelnemer.html.twig', [
			'activiteit' => $activiteit,
			'deelnemer' => $deelnemer,
			'naamweergave' => InstellingUtil::instelling(
				'maaltijden',
				'weergave_ledennamen_maaltijdlijst'
			),
		]);
	}

	/**
	 * @param AanmeldActiviteit $activiteit
	 * @param Profiel $lid
	 * @param bool $aanwezig
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/lijst/{activiteit}/aanwezig/{lid}/{aanwezig}',
			methods: ['POST']
		)
	]
	public function lijstAanwezig(
		AanmeldActiviteit $activiteit,
		Profiel $lid,
		bool $aanwezig
	): Response {
		if (!$activiteit->magLijstBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$deelnemer = $this->deelnemerRepository->setAanwezig(
			$activiteit,
			$lid,
			$aanwezig
		);
		return $this->render('aanmelder/onderdelen/deelnemer.html.twig', [
			'activiteit' => $activiteit,
			'deelnemer' => $deelnemer,
			'naamweergave' => InstellingUtil::instelling(
				'maaltijden',
				'weergave_ledennamen_maaltijdlijst'
			),
		]);
	}
}
