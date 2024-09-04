<?php

namespace CsrDelft\model\entity\profiel;

/**
 * ProfielLogValueChangeCensuur.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * Gencensureerde wijziging van een waarde in het profiel.
 *
 */
class ProfielLogValueChangeCensuur extends AbstractProfielLogValueChangeEntry
{
	public function __construct($property, public $oldEmpty, public $newEmpty)
	{
		parent::__construct($property);
	}

	/**
	 * @return string
	 */
	public function toHtml()
	{
		$old = $this->oldEmpty ? '' : '[GECENSUREERD]';
		$new = $this->newEmpty ? '' : '[GECENSUREERD]';
		return "($this->field) $old => $new";
	}
}
