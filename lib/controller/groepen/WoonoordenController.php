<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Woonoord;
use Doctrine\Persistence\ManagerRegistry;

/**
 * WoonoordenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor woonoorden en huizen.
 */
class WoonoordenController extends AbstractGroepenController
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Woonoord::class);
    }
}
