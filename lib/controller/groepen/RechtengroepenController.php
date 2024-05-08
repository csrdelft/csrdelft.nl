<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\RechtenGroep;

/**
 * RechtengroepenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor rechten-groepen. Kleine letter g vanwege groepen-router.
 */
class RechtengroepenController extends AbstractGroepenController
{
	public function getGroepType()
	{
		return RechtenGroep::class;
	}
}
