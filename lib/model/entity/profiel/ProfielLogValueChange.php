<?php

namespace CsrDelft\model\entity\profiel;

/**
 * ProfielLogValueChange.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * Log voor wijziging van een waarde in het profiel, met betreffende waarde.
 *
 */
class ProfielLogValueChange extends AbstractProfielLogValueChangeEntry {

	/**
	 * Oude waarde
	 * @var string
	 */
	public $oldValue;

	/**
	 * Nieuwe waarde
	 * @var string
	 */
	public $newValue;

	public function __construct($property, $oldValue, $newValue) {
		parent::__construct($property);
		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
	}

	public function toHtml() {
		return "($this->field) ".htmlspecialchars($this->oldValue)." => ".htmlspecialchars($this->newValue);
	}

	public function censureer() {
		$oldEmpty = trim($this->oldValue) === '';
		$newEmpty = trim($this->newValue) === '';
		return new ProfielLogValueChangeCensuur($this->field, $oldEmpty, $newEmpty);
	}

	public function censureerVeld($naam) {
		if ($this->field == $naam) {
			return $this->censureer();
		} else {
			return $this;
		}
	}

}
