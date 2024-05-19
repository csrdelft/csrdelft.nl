<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Activiteit;

/**
 * ApiActiviteitenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor activiteiten.
 */
class ActiviteitenController extends KetzersController
{
	public function getGroepType(): string
	{
		return Activiteit::class;
	}
}
