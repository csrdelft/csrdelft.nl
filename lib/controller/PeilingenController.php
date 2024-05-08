<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Security\Voter\Entity\PeilingVoter;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\service\PeilingenService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\peilingen\PeilingForm;
use CsrDelft\view\peilingen\PeilingTable;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingenController extends AbstractController
{
	/** @var PeilingenRepository */
	private $peilingenRepository;
	/** @var PeilingenService */
	private $peilingenService;

	public function __construct(
		PeilingenRepository $peilingenRepository,
		PeilingenService $peilingenService
	) {
		$this->peilingenRepository = $peilingenRepository;
		$this->peilingenService = $peilingenService;
	}

	/**
  * @param Peiling|null $peiling
  * @return Response
  * @Auth(P_PEILING_EDIT)
  */
 #[Route(path: '/peilingen/beheer/{id}', methods: ['GET'], requirements: ['id' => '\d+'], defaults: ['id' => null])]
 public function table(Peiling $peiling = null): Response
	{
		// Laat een modal zien als een specifieke peiling bewerkt wordt
		if ($peiling) {
			$table = new PeilingTable();
			$table->setSearch($peiling->titel);

			$this->denyAccessUnlessGranted(PeilingVoter::BEWERKEN, $peiling);

			$form = $this->createFormulier(PeilingForm::class, $peiling, [
				'action' => $this->generateUrl('csrdelft_peilingen_bewerken'),
				'nieuw' => false,
				'dataTableId' => $table->getDataTableId(),
			]);

			return $this->render('default.html.twig', [
				'content' => $table,
				'modal' => $form->createModalView(),
			]);
		} else {
			return $this->render('default.html.twig', [
				'content' => new PeilingTable(),
			]);
		}
	}

	/**
  * @return GenericDataTableResponse
  * @Auth(P_PEILING_EDIT)
  */
 #[Route(path: '/peilingen/beheer', methods: ['POST'])]
 public function lijst(): GenericDataTableResponse
	{
		return $this->tableData(
			$this->peilingenRepository->getPeilingenVoorBeheer()
		);
	}

	/**
  * @param Request $request
  * @return GenericDataTableResponse|Response
  * @Auth(P_PEILING_EDIT)
  */
 #[Route(path: '/peilingen/nieuw', methods: ['POST'])]
 public function nieuw(Request $request)
	{
		$peiling = new Peiling();

		$form = $this->createFormulier(PeilingForm::class, $peiling, [
			'action' => $this->generateUrl('csrdelft_peilingen_nieuw'),
			'nieuw' => true,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$peiling->eigenaarProfiel = $this->getProfiel();
			$peiling->mag_bewerken = false;

			$this->getDoctrine()
				->getManager()
				->persist($peiling);
			$this->getDoctrine()
				->getManager()
				->flush();

			return $this->tableData([$peiling]);
		}

		return new Response($form->createModalView());
	}

	/**
  * @param Request $request
  * @return GenericDataTableResponse|Response
  * @Auth(P_PEILING_EDIT)
  */
 #[Route(path: '/peilingen/bewerken', methods: ['POST'])]
 public function bewerken(Request $request)
	{
		$selection = $this->getDataTableSelection();

		if ($selection) {
			$peiling = $this->peilingenRepository->retrieveByUUID($selection[0]);
		} else {
			// Hier is de id in post gezet
			//			$peiling = new Peiling();
			$id = $request->request->get('id');
			$peiling = $this->peilingenRepository->find($id);
		}

		$this->denyAccessUnlessGranted(
			PeilingVoter::BEWERKEN,
			$peiling,
			'Je mag deze peiling niet bewerken!'
		);

		$form = $this->createFormulier(PeilingForm::class, $peiling, [
			'action' => $this->generateUrl('csrdelft_peilingen_bewerken'),
			'nieuw' => false,
			'dataTableId' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->persist($peiling);
			$this->getDoctrine()
				->getManager()
				->flush();

			return $this->tableData([$peiling]);
		}

		return new Response($form->createModalView());
	}

	/**
  * @return GenericDataTableResponse
  * @Auth(P_PEILING_MOD)
  */
 #[Route(path: '/peilingen/verwijderen', methods: ['GET', 'POST'])]
 public function verwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		$peiling = $this->peilingenRepository->retrieveByUUID($selection[0]);
		$removed = new RemoveDataTableEntry($peiling->id, Peiling::class);

		$this->denyAccessUnlessGranted(PeilingVoter::BEWERKEN, $peiling);

		$this->peilingenRepository->delete($peiling);

		return $this->tableData([$removed]);
	}

	/**
  * @param Request $request
  * @param int $id
  * @return JsonResponse
  * @Auth(P_PEILING_VOTE)
  */
 #[IsGranted("stemmen", subject: "peiling")]
 #[Route(path: '/peilingen/stem/{id}', methods: ['POST'], requirements: ['id' => '\d+'])]
	public function stem(Request $request, Peiling $peiling): JsonResponse
	{
		$ids = $request->request->filter('opties', [], FILTER_VALIDATE_INT);

		if ($this->peilingenService->stem($peiling, $ids, $this->getProfiel())) {
			return new JsonResponse(true);
		} else {
			return new JsonResponse(false, 400);
		}
	}
}
