<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\RechtenGroepenModel;

/**
 * RechtengroepenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor rechten-groepen. Kleine letter g vanwege groepen-router.
 */
class RechtengroepenController extends AbstractGroepenController {
	public function __construct(RechtenGroepenModel $rechtenGroepenModel) {
		parent::__construct($rechtenGroepenModel);
	}
}
