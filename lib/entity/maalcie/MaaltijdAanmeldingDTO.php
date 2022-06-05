<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\entity\profiel\Profiel;

class MaaltijdAanmeldingDTO
{
	/**
	 * @var Profiel
	 */
	public $voor_lid;
	/**
	 * @var int
	 */
	public $aantal_gasten = 0;
}
