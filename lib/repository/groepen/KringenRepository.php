<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Kring;
use CsrDelft\repository\GroepRepository;
use Doctrine\Persistence\ManagerRegistry;

class KringenRepository extends GroepRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kring::class);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy($criteria, ['verticale' => 'ASC', 'kringNummer' => 'ASC'] + ($orderBy ?? []), $limit, $offset);
    }

    public function get($id)
    {
        if (is_numeric($id)) {
            return parent::get($id);
        }
        list($verticale, $kringNummer) = explode('.', $id);
        return $this->findOneBy(['verticale' => $verticale, 'kringNummer' => $kringNummer]);
    }

    public function nieuw($letter = null)
    {
        /** @var Kring $kring */
        $kring = parent::nieuw();
        $kring->verticale = $letter;
        return $kring;
    }
}
