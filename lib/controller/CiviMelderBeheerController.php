<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\view\civimelder\ActiviteitForm;
use CsrDelft\view\civimelder\ActiviteitTabel;
use CsrDelft\view\civimelder\ReeksForm;
use CsrDelft\view\civimelder\ReeksTabel;
use CsrDelft\view\datatable\GenericDataTableResponse;
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

	public function __construct(ReeksRepository $reeksRepository,
															ActiviteitRepository $activiteitRepository)
	{
		$this->reeksRepository = $reeksRepository;
		$this->activiteitRepository = $activiteitRepository;
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
}
