<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\RechtenGroep;
use Doctrine\Persistence\ManagerRegistry;

/**
 * RechtengroepenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor rechten-groepen. Kleine letter g vanwege groepen-router.
 */
class RechtengroepenController extends AbstractGroepenController
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, RechtenGroep::class);
	}
}
