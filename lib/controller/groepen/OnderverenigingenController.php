<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Ondervereniging;

/**
 * OnderverenigingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor onderverenigingen.
 */
class OnderverenigingenController extends AbstractGroepenController
{
	public function getGroepType()
	{
		return Ondervereniging::class;
	}
}
