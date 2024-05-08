<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Commissie;

/**
 * CommissiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissies.
 */
class CommissiesController extends AbstractGroepenController
{
	public function getGroepType()
	{
		return Commissie::class;
	}
}
