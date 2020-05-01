<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\groepen\RechtenGroepenRepository;

/**
 * RechtengroepenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor rechten-groepen. Kleine letter g vanwege groepen-router.
 */
class RechtengroepenController extends AbstractGroepenController {
	public function __construct(RechtenGroepenRepository $rechtenGroepenRepository) {
		parent::__construct($rechtenGroepenRepository);
	}
}
