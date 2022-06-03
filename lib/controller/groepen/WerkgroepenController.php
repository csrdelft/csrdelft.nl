<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Werkgroep;
use Doctrine\Persistence\ManagerRegistry;


/**
 * WerkgroepenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor werkgroepen.
 *
 * N.B. Een Werkgroep extends Ketzer, maar de controller niet om de "nieuwe ketzer"-wizard te vermijden.
 */
class WerkgroepenController extends AbstractGroepenController
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Werkgroep::class);
    }
}
