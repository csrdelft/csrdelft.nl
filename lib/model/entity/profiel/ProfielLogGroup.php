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
	 * @param string $editor
	 * @param DateTime $timestamp
	 */
	public function __construct(
		/**
		 * UID of editor
		 */
		public $editor,
		public $timestamp
	) {
	}

	abstract public function toHtml();

	public function censureerVeld($naam): bool
	{
		return false;
	}
}
