<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Woonoord;

/**
 * WoonoordenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor woonoorden en huizen.
 */
class WoonoordenController extends AbstractGroepenController
{
	/**
	 * @return string
	 *
	 * @psalm-return Woonoord::class
	 */
	public function getGroepType()
	{
		return Woonoord::class;
	}
}
