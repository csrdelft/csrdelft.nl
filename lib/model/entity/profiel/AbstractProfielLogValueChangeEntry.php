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
abstract class AbstractProfielLogValueChangeEntry extends
	AbstractProfielLogChangeEntry
{
	/**
	 * @param string $property
	 */
	public function __construct(public $field)
	{
	}
}
