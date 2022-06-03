<?php

namespace CsrDelft\repository\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package CsrDelft\repository\commissievoorkeuren
 * @method VoorkeurOpmerking|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoorkeurOpmerking|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoorkeurOpmerking[]    findAll()
 * @method VoorkeurOpmerking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoorkeurOpmerkingRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoorkeurOpmerking::class);
    }

    /**
     * @param Profiel $profiel
     * @return VoorkeurOpmerking
     */
    public function getOpmerkingVoorLid(Profiel $profiel)
    {
        $result = $this->find($profiel->uid);
        if ($result == false) {
            $result = new VoorkeurOpmerking();
            $result->profiel = $profiel;
            $result->uid = $profiel->uid;
        }
        return $result;
    }
}
