<?php


namespace CsrDelft\model\entity\interfaces;


use CsrDelft\common\Enum;

interface HeeftSoort {
	/**
	 * @return Enum
	 */
	public function getSoort();

	public function setSoort($soort);
}
