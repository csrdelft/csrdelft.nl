<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\OnderverenigingenRepository;

/**
 * OnderverenigingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor onderverenigingen.
 */
class OnderverenigingenController extends AbstractGroepenController {
	public function __construct(ChangeLogRepository $changeLogRepository, OnderverenigingenRepository $onderverenigingenRepository) {
		parent::__construct($changeLogRepository, $onderverenigingenRepository);
	}
}
