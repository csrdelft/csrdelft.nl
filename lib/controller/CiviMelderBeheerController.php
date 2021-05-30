<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
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

	public function __construct(ReeksRepository $reeksRepository)
	{
		$this->reeksRepository = $reeksRepository;
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
	 * @Route("/reeks/bewerken", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function bewerken(Request $request): Response
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
			'action' => $this->generateUrl('csrdelft_civimelderbeheer_bewerken'),
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
