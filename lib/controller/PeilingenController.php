<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\service\PeilingenService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\peilingen\PeilingForm;
use CsrDelft\view\peilingen\PeilingTable;
use CsrDelft\view\View;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingenController extends AbstractController {
	/** @var PeilingenRepository */
	private $peilingenRepository;
	/** @var PeilingenService */
	private $peilingenService;

	public function __construct(PeilingenRepository $peilingenRepository, PeilingenService $peilingenService) {
		$this->peilingenRepository = $peilingenRepository;
		$this->peilingenService = $peilingenService;
	}

	/**
	 * @param Peiling|null $peiling
	 * @return View
	 * @Route("/peilingen/beheer/{id}", methods={"GET"}, requirements={"id": "\d+"}, defaults={"id": null})
	 * @Auth(P_PEILING_EDIT)
	 */
	public function table(Peiling $peiling = null) {
		// Laat een modal zien als een specifieke peiling bewerkt wordt
		if ($peiling) {
			$table = new PeilingTable();
			$table->setSearch($peiling->titel);
			$form = new PeilingForm($peiling, false);
			$form->setDataTableId($table->getDataTableId());

			return view('default', ['content' => $table, 'modal' => $form]);
		} else {
			return view('default', ['content' => new PeilingTable()]);
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/peilingen/beheer", methods={"POST"})
	 * @Auth(P_PEILING_EDIT)
	 */
	public function lijst() {
		return $this->tableData($this->peilingenRepository->getPeilingenVoorBeheer());
	}

	/**
	 * @param EntityManagerInterface $em
	 * @return GenericDataTableResponse|PeilingForm
	 * @throws ORMException
	 * @Route("/peilingen/nieuw", methods={"POST"})
	 * @Auth(P_PEILING_EDIT)
	 */
	public function nieuw(EntityManagerInterface $em) {
		$peiling = new Peiling();
		$form = new PeilingForm($peiling, true);

		if ($form->isPosted() && $form->validate()) {
			$peiling = $form->getModel();
			$peiling->eigenaarProfiel = $this->getProfiel();
			$peiling->mag_bewerken = false;

			$this->getDoctrine()->getManager()->persist($peiling);
			$this->getDoctrine()->getManager()->flush();

			return $this->tableData([$peiling]);
		}

		return $form;
	}

	/**
	 * @return GenericDataTableResponse|PeilingForm
	 * @throws CsrGebruikerException
	 * @Route("/peilingen/bewerken", methods={"POST"})
	 * @Auth(P_PEILING_EDIT)
	 */
	public function bewerken() {
		$selection = $this->getDataTableSelection();

		if ($selection) {
			$peiling = $this->peilingenRepository->retrieveByUUID($selection[0]);

			if (!$this->peilingenRepository->magBewerken($peiling)) {
				throw new CsrGebruikerException('Je mag deze peiling niet bewerken!');
			}
		} else {
			// Hier is de id in post gezet
			$peiling = new Peiling();
		}

		$form = new PeilingForm($peiling, false);
		if ($form->isPosted() && $form->validate()) {
			$peiling = $form->getModel();
			$this->getDoctrine()->getManager()->persist($peiling);
			$this->getDoctrine()->getManager()->flush();

			return $this->tableData([$peiling]);
		}

		return $form;
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/peilingen/verwijderen", methods={"GET", "POST"})
	 * @Auth(P_PEILING_MOD)
	 */
	public function verwijderen() {
		$selection = $this->getDataTableSelection();
		$peiling = $this->peilingenRepository->retrieveByUUID($selection[0]);
		$removed = new RemoveDataTableEntry($peiling->id, Peiling::class);

		$this->peilingenRepository->delete($peiling);

		return $this->tableData([$removed]);
	}

	/**
	 * @param Request $request
	 * @param int $id
	 * @return View
	 * @Route("/peilingen/stem/{id}", methods={"POST"}, requirements={"id": "\d+"})
	 * @Auth(P_PEILING_VOTE)
	 */
	public function stem(Request $request, $id) {
		$ids = $request->request->filter('opties', [], FILTER_VALIDATE_INT);

		if($this->peilingenService->stem($id, $ids, $this->getUid())) {
			return new JsonResponse(true);
		} else {
			return new JsonResponse(false, 400);
		}
	}
}
