<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Activiteit;
use Doctrine\Persistence\ManagerRegistry;


/**
 * ApiActiviteitenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor activiteiten.
 */
class ActiviteitenController extends KetzersController
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Activiteit::class);
	}
}
