<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;

/**
 * WoonoordenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor woonoorden en huizen.
 */
class WoonoordenController extends AbstractGroepenController {
	public function __construct(ChangeLogRepository $changeLogRepository, WoonoordenRepository $woonoordenRepository) {
		parent::__construct($changeLogRepository, $woonoordenRepository);
	}
}
