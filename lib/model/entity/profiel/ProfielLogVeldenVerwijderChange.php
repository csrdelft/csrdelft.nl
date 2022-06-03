<?php

namespace CsrDelft\model\entity\profiel;

/**
 * ProfielLogVeldenVerwijderChange.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * Log voor verwijderen van profielvelden.
 *
 */
class ProfielLogVeldenVerwijderChange extends AbstractProfielLogChangeEntry {
	/**
	 * @var string[]
	 */
	public $velden = [];
	public function __construct($velden) {
		$this->velden = $velden;
	}

	public function toHtml() {
		return "Verwijder velden: ". implode(", ", $this->velden);
	}
}