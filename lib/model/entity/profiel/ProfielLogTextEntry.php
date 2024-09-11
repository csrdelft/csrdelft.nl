<?php

namespace CsrDelft\model\entity\profiel;

/**
 * ProfielLogTextEntry.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * Textuele toelichting op wijziging aan profiel.
 *
 */
class ProfielLogTextEntry extends AbstractProfielLogEntry
{
	public function __construct(public $text)
	{
	}

	public function toHtml()
	{
		return htmlspecialchars((string) $this->text);
	}
}
