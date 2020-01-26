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
abstract class ProfielLogGroup {
	/**
	 * UID of editor
	 * @var string
	 */
	public $editor;

	/**
	 * @var DateTime
	 */
	public $timestamp;

	public function __construct($editor, $timestamp) {
		$this->editor = $editor;
		$this->timestamp = $timestamp;
	}

	public abstract function toHtml();

	public function censureerVeld($naam) : bool {
		return false;
	}

}
