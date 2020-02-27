<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\peilingen\PeilingForm;
use CsrDelft\view\peilingen\PeilingTable;
use CsrDelft\view\View;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingenController extends AbstractController {
	/** @var PeilingenRepository */
	private $peilingenRepository;
	/** @var PeilingenLogic */
	private $peilingenLogic;

	public function __construct(PeilingenRepository $peilingenRepository, PeilingenLogic $peilingenLogic) {
		$this->peilingenRepository = $peilingenRepository;
		$this->peilingenLogic = $peilingenLogic;
	}

	/**
	 * @param null $id
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function table($id = null) {
		// Laat een modal zien als een specifieke peiling bewerkt wordt
		if ($id) {
			$table = new PeilingTable();
			$peiling = $this->peilingenRepository->find($id);
			$table->setSearch($peiling->titel);
			$form = new PeilingForm($peiling, false);
			$form->setDataTableId($table->getDataTableId());

			return view('default', ['content' => $table, 'modal' => $form]);
		} else {
			return view('default', ['content' => new PeilingTable()]);
		}
	}

	/**
	 * @return Response
	 */
	public function lijst() {
		return $this->tableData($this->peilingenRepository->getPeilingenVoorBeheer());
	}

	/**
	 * @param EntityManagerInterface $em
	 * @return Response|View
	 * @throws ORMException
	 */
	public function nieuw(EntityManagerInterface $em) {
		$peiling = new Peiling();
		$form = new PeilingForm($peiling, true);

		if ($form->isPosted() && $form->validate()) {
			$peiling = $form->getModel();
			$peiling->eigenaarProfiel = $em->getReference(Profiel::class, LoginModel::getUid());
			$peiling->mag_bewerken = false;

			$this->getDoctrine()->getManager()->persist($peiling);
			$this->getDoctrine()->getManager()->flush();

			return $this->tableData([$peiling]);
		}

		return $form;
	}

	/**
	 * @return Response|View
	 * @throws CsrGebruikerException
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
	 * @return Response
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
	 */
	public function stem(Request $request, $id) {
		$ids = $request->request->filter('opties', [], FILTER_VALIDATE_INT);

		if($this->peilingenLogic->stem($id, $ids, LoginModel::getUid())) {
			return new JsonResponse(true);
		} else {
			return new JsonResponse(false, 400);
		}
	}
}
