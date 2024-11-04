<?php

namespace CsrDelft\model\entity\profiel;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\repository\ProfielRepository;

/**
 * ProfielUpdateLogGroup.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * LogGroup uit het legacy log die nog niet geparsed is.
 *
 */
class ProfielUpdateLogGroup extends ProfielLogGroup
{


	/**
	 * Censureer alle velden met gegeven naam
	 *
	 * @param $naam
	 *
	 * @return false|float|int Of er data gecensureerd is
	 */
	public function censureerVeld($naam): int|float|false
	{
		$data_verwijderd = false;
		for ($i = 0; $i < sizeof($this->entries); $i++) {
			$gecensureerd = $this->entries[$i]->censureerVeld($naam);
			$data_verwijderd |= $gecensureerd !== $this->entries[$i];
			$this->entries[$i] = $gecensureerd;
		}
		return $data_verwijderd;
	}
}
