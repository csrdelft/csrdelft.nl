<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\peilingen\PeilingOptie;
use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\peilingen\PeilingOptiesRepository;
use CsrDelft\view\peilingen\PeilingOptieForm;
use CsrDelft\view\peilingen\PeilingOptieTable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 *
 * Voor routes in /peilingen/opties
 */
class PeilingOptiesController extends AbstractController {
	/** @var PeilingenLogic */
	private $peilingenLogic;
	/** @var PeilingOptiesRepository */
	private $peilingOptiesRepository;

	public function __construct(PeilingOptiesRepository $peilingOptiesRepository, PeilingenLogic $peilingenLogic) {
		$this->peilingOptiesRepository = $peilingOptiesRepository;
		$this->peilingenLogic = $peilingenLogic;
	}

	public function table($id) {
		return new PeilingOptieTable($id);
	}

	public function lijst($id) {
		return $this->tableData($this->peilingOptiesRepository->findBy(['peiling_id' => $id]));
	}

	/**
	 * @param EntityManagerInterface $em
	 * @param $id
	 * @return PeilingOptieForm|Response
	 * @throws ORMException
	 */
	public function toevoegen(EntityManagerInterface $em, $id) {
		$form = new PeilingOptieForm(new PeilingOptie(), $id);

		if (!$this->peilingenLogic->magOptieToevoegen($id)) {
			throw new CsrGebruikerException("Mag geen opties meer toevoegen!");
		}

		if ($form->isPosted() && $form->validate()) {
			/** @var PeilingOptie $optie */
			$optie = $form->getModel();
			$optie->ingebracht_door = LoginModel::getUid();
			$optie->peiling = $em->getReference(Peiling::class, $id);

			$this->getDoctrine()->getManager()->persist($optie);
			$this->getDoctrine()->getManager()->flush();
			return $this->tableData([$optie]);
		}

		return $form;
	}

	/**
	 * @return Response
	 * @throws CsrGebruikerException
	 */
	public function verwijderen() {
		$selection = $this->getDataTableSelection();

		/** @var PeilingOptie|false $peilingOptie */
		$peilingOptie = $this->peilingOptiesRepository->retrieveByUUID($selection[0]);

		if ($peilingOptie !== false && $peilingOptie->stemmen == 0) {
			$this->getDoctrine()->getManager()->remove($peilingOptie);
			$removed = new RemoveDataTableEntry($peilingOptie->id, PeilingOptie::class);
			$this->getDoctrine()->getManager()->flush();
			return $this->tableData([$removed]);
		} else {
			throw new CsrGebruikerException('Peiling optie bestaat niet of er is al een keer op gestemd.');
		}
	}
}
