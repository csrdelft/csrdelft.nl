<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Commissie;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CommissiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissies.
 */
class CommissiesController extends AbstractGroepenController {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Commissie::class);
	}
}
