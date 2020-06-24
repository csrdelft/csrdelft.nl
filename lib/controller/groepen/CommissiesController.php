<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\CommissiesRepository;

/**
 * CommissiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissies.
 */
class CommissiesController extends AbstractGroepenController {
	public function __construct(ChangeLogRepository $changeLogRepository, CommissiesRepository $commissiesRepository) {
		parent::__construct($changeLogRepository, $commissiesRepository);
	}
}
