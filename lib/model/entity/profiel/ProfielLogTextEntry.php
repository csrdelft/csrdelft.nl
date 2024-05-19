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
	public $text;

	public function __construct($text)
	{
		$this->text = $text;
	}

	public function toHtml(): string
	{
		return htmlspecialchars($this->text);
	}
}
