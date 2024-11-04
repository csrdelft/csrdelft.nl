<?php

namespace CsrDelft\model\entity\profiel;

use DateTime;

/**
 * ProfielLogGroup.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * Verzameling van wijzigingen aan een profiel, met dezelfde datum en auteur.
 *
 */
abstract class ProfielLogGroup
{


	/**
	 * @return false
	 */
	public function censureerVeld(string $naam): bool
	{
		return false;
	}
}
