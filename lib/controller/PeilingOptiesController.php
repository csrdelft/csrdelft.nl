<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Security\Voter\Entity\PeilingVoter;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\peilingen\PeilingOptie;
use CsrDelft\repository\peilingen\PeilingOptiesRepository;
use CsrDelft\service\PeilingenService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\peilingen\PeilingOptieForm;
use CsrDelft\view\peilingen\PeilingOptieTable;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 *
 * Voor routes in /peilingen/opties
 */
class PeilingOptiesController extends AbstractController
{
	/** @var PeilingOptiesRepository */
	private $peilingOptiesRepository;

	public function __construct(PeilingOptiesRepository $peilingOptiesRepository)
	{
		$this->peilingOptiesRepository = $peilingOptiesRepository;
	}

	/**
	 * @param $id
	 * @return PeilingOptieTable
	 * @Route("/peilingen/opties/{id}", methods={"GET"}, requirements={"id": "\d+"})
	 * @Auth(P_PEILING_EDIT)
	 */
	public function table($id): PeilingOptieTable
	{
		return new PeilingOptieTable($id);
	}

	/**
	 * @param $id
	 * @return GenericDataTableResponse
	 * @Route("/peilingen/opties/{id}", methods={"POST"}, requirements={"id": "\d+"})
	 * @Auth(P_PEILING_EDIT)
	 */
	#[IsGranted("bekijken", subject: "peiling")]
	public function lijst(Peiling $peiling): GenericDataTableResponse
	{
		return $this->tableData(
			$this->peilingOptiesRepository->findBy(['peiling_id' => $peiling->id])
		);
	}

	/**
	 * @param Peiling $peiling
	 * @return GenericDataTableResponse|PeilingOptieForm
	 * @Route("/peilingen/opties/{id}/toevoegen", methods={"POST"}, requirements={"id": "\d+"})
	 * @Auth(P_PEILING_VOTE)
	 */
	public function toevoegen(Peiling $peiling)
	{
		$form = new PeilingOptieForm(new PeilingOptie(), $peiling->id);

		$this->denyAccessUnlessGranted(
			PeilingVoter::TOEVOEGEN,
			$peiling,
			'Mag geen opties meer toevoegen'
		);

		if ($form->isPosted() && $form->validate()) {
			/** @var PeilingOptie $optie */
			$optie = $form->getModel();
			$optie->ingebracht_door = $this->getUid();
			$optie->peiling = $peiling;

			$this->getDoctrine()
				->getManager()
				->persist($optie);
			$this->getDoctrine()
				->getManager()
				->flush();
			return $this->tableData([$optie]);
		}

		return $form;
	}

	/**
	 * @throws CsrGebruikerException
	 * @return GenericDataTableResponse
	 * @Route("/peilingen/opties/verwijderen", methods={"POST"})
	 * @Auth(P_PEILING_EDIT)
	 */
	public function verwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();

		/** @var PeilingOptie|false $peilingOptie */
		$peilingOptie = $this->peilingOptiesRepository->retrieveByUUID(
			$selection[0]
		);

		$this->denyAccessUnlessGranted(
			PeilingVoter::BEWERKEN,
			$peilingOptie->peiling
		);

		if ($peilingOptie && $peilingOptie->stemmen == 0) {
			$this->getDoctrine()
				->getManager()
				->remove($peilingOptie);
			$removed = new RemoveDataTableEntry(
				$peilingOptie->id,
				PeilingOptie::class
			);
			$this->getDoctrine()
				->getManager()
				->flush();
			return $this->tableData([$removed]);
		} else {
			throw new CsrGebruikerException(
				'Peiling optie bestaat niet of er is al een keer op gestemd.'
			);
		}
	}
}
