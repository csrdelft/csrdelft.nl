<?php

namespace CsrDelft\entity\groepen\interfaces;

use CsrDelft\common\Enum;

interface HeeftSoort
{
	/**
	 * @return Enum
	 */
	public function getSoort();

	public function setSoort($soort);

	public function setSoortString($soort);
}
