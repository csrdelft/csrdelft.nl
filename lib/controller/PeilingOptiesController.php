<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\peilingen\PeilingOptie;
use CsrDelft\repository\peilingen\PeilingOptiesRepository;
use CsrDelft\service\PeilingenService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\peilingen\PeilingOptieForm;
use CsrDelft\view\peilingen\PeilingOptieTable;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 *
 * Voor routes in /peilingen/opties
 */
class PeilingOptiesController extends AbstractController
{
    /** @var PeilingenService */
    private $peilingenService;
    /** @var PeilingOptiesRepository */
    private $peilingOptiesRepository;

    public function __construct(PeilingOptiesRepository $peilingOptiesRepository, PeilingenService $peilingenService)
    {
        $this->peilingOptiesRepository = $peilingOptiesRepository;
        $this->peilingenService = $peilingenService;
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
    public function lijst($id): GenericDataTableResponse
    {
        return $this->tableData($this->peilingOptiesRepository->findBy(['peiling_id' => $id]));
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

        if (!$this->peilingenService->magOptieToevoegen($peiling)) {
            throw new CsrGebruikerException("Mag geen opties meer toevoegen!");
        }

        if ($form->isPosted() && $form->validate()) {
            /** @var PeilingOptie $optie */
            $optie = $form->getModel();
            $optie->ingebracht_door = $this->getUid();
            $optie->peiling = $peiling;

            $this->getDoctrine()->getManager()->persist($optie);
            $this->getDoctrine()->getManager()->flush();
            return $this->tableData([$optie]);
        }

        return $form;
    }

    /**
     * @return GenericDataTableResponse
     * @Route("/peilingen/opties/verwijderen", methods={"POST"})
     * @Auth(P_PEILING_EDIT)
     * @throws CsrGebruikerException
     */
    public function verwijderen(): GenericDataTableResponse
    {
        $selection = $this->getDataTableSelection();

        /** @var PeilingOptie|false $peilingOptie */
        $peilingOptie = $this->peilingOptiesRepository->retrieveByUUID($selection[0]);

        if ($peilingOptie && $peilingOptie->stemmen == 0) {
            $this->getDoctrine()->getManager()->remove($peilingOptie);
            $removed = new RemoveDataTableEntry($peilingOptie->id, PeilingOptie::class);
            $this->getDoctrine()->getManager()->flush();
            return $this->tableData([$removed]);
        } else {
            throw new CsrGebruikerException('Peiling optie bestaat niet of er is al een keer op gestemd.');
        }
    }
}
