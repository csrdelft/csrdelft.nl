<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Ondervereniging;
use Doctrine\Persistence\ManagerRegistry;

/**
 * OnderverenigingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor onderverenigingen.
 */
class OnderverenigingenController extends AbstractGroepenController
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Ondervereniging::class);
	}
}
