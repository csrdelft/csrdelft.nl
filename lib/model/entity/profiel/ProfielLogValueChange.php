<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 25-5-18
 * Time: 21:23
 */

namespace CsrDelft\model\entity\profiel;


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
		$this->field = $property;
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


}