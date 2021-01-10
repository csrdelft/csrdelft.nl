<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\civimelder\ReeksForm;
use CsrDelft\view\civimelder\ReeksTabel;
use CsrDelft\view\datatable\GenericDataTableResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;

use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\repository\civimelder\DeelnemerRepository;
use CsrDelft\repository\civimelder\ReeksRepository;

class CiviMelderBeheerController extends AbstractController {
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var DeelnemerRepository
	 */
	private $deelnemerRepository;
	/**
	 * @var ActiviteitRepository
	 */
	private $activiteitRepository;
	/**
	 * @var ReeksRepository
	 */
	private $reeksRepository;

	public function __construct(ProfielRepository $profielRepository,
															DeelnemerRepository $deelnemerRepository,
															ActiviteitRepository $activiteitRepository,
															ReeksRepository $reeksRepository) {
		$this->profielRepository = $profielRepository;
		$this->deelnemerRepository = $deelnemerRepository;
		$this->activiteitRepository = $activiteitRepository;
		$this->reeksRepository = $reeksRepository;
	}

	/**
	 * @Route("/civimelder/beheer", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function beheerTabel() {
		return $this->render('default.html.twig', ['content' => new ReeksTabel()]);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/civimelder/beheer", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijst() {
		// TODO: Filter based on rights
		return $this->tableData($this->reeksRepository->findAll());
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|Response
	 * @Route("/civimelder/reeks/bewerken", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function bewerken(Request $request) {
		$selection = $this->getDataTableSelection();

		if ($selection) {
			$reeks = $this->reeksRepository->retrieveByUUID($selection[0]);
		} else {
			throw new CsrGebruikerException('Geen reeks geselecteerd');
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
	 * @Route("/civimelder/reeks/{id}", methods={"GET"})
	 * @param Reeks $reeks
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	public function reeksDetail(Reeks $reeks) {
		// TODO: Check
		return new Response('Hallo: ' . $reeks->getNaam());
	}
}
