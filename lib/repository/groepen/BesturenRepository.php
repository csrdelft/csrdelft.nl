<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\repository\GroepRepository;
use Doctrine\Persistence\ManagerRegistry;

class BesturenRepository extends GroepRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bestuur::class);
    }

    public function nieuw($soort = null)
    {
        /** @var Bestuur $bestuur */
        $bestuur = parent::nieuw();
        $bestuur->bijbeltekst = '';
        return $bestuur;
    }
}
