<?php

namespace CsrDelft\repository;

use CsrDelft\entity\Streeplijst;
use CsrDelft\service\security\LoginService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author J. de Jong
 *
 * Bekijken of bewerken van Streeplijsts.
 * @method Streeplijst|null find($id, $lockMode = null, $lockVersion = null)
 * @method Streeplijst|null findOneBy(array $criteria, array $orderBy = null)
 * @method Streeplijst[]    findAll()
 * @method Streeplijst[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreeplijstRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Streeplijst::class);
    }

    /**
     * @return Streeplijst[]
     */
    public function getAlleStreeplijsten()
    {
        return $this->findBy(['maker' => LoginService::getUid()], ['aanmaakdatum' => 'ASC']);
    }

    /**
     * @param string $naam_streeplijst
     * @param string $leden_streeplijst
     * @param string $inhoud_streeplijst
     * @return Streeplijst
     */
    public function nieuw($naam_streeplijst, $leden_streeplijst, $inhoud_streeplijst)
    {
        $streeplijst = new Streeplijst();
        $streeplijst->maker = LoginService::getUid();
        $streeplijst->aanmaakdatum = date_create_immutable();
        $streeplijst->inhoud_streeplijst = $inhoud_streeplijst;
        $streeplijst->naam_streeplijst = $naam_streeplijst;
        $streeplijst->leden_streeplijst = $leden_streeplijst;
        return $streeplijst;
    }

    /**
     * @param int $id
     * @param string $naam_streeplijst
     */
    public function updateNaam($id, $naam_streeplijst)
    {
        $streeplijst = $this->find($id);
        $streeplijst->naam_streeplijst = $naam_streeplijst;
    }

    /**
     * @param int $id
     * @param string $leden_streeplijst
     * @param string $inhoud_streeplijst
     */
    public function updateInhoud($id, $leden_streeplijst, $inhoud_streeplijst)
    {
        $streeplijst = $this->find($id);
        $streeplijst->inhoud_streeplijst = $inhoud_streeplijst;
        $streeplijst->leden_streeplijst = $leden_streeplijst;
    }

}
