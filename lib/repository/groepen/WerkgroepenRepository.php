<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Werkgroep;

class WerkgroepenRepository extends KetzersRepository
{
	public function getEntityClassName(): string
	{
		return Werkgroep::class;
	}
}
