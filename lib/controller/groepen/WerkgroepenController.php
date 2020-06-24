<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\WerkgroepenRepository;


/**
 * WerkgroepenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor werkgroepen.
 *
 * N.B. Een Werkgroep extends Ketzer, maar de controller niet om de "nieuwe ketzer"-wizard te vermijden.
 */
class WerkgroepenController extends AbstractGroepenController {
	public function __construct(ChangeLogRepository $changeLogRepository, WerkgroepenRepository $werkgroepenRepository) {
		parent::__construct($changeLogRepository, $werkgroepenRepository);
	}
}
