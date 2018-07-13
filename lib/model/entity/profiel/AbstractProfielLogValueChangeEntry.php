<?php

namespace CsrDelft\model\entity\profiel;

/**
 * AbstractProfielLogValueChangeEntry.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * LogEntry voor verandering van profielwaarde.
 *
 */
abstract class AbstractProfielLogValueChangeEntry extends AbstractProfielLogChangeEntry {

	/**
	 * @var string
	 */
	public $field;

	public function __construct($property) {
		$this->field = $property;
	}
}