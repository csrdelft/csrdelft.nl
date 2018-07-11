<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 25-5-18
 * Time: 21:27
 */

namespace CsrDelft\model\entity\profiel;


class ProfielLogValueChangeCensuur extends AbstractProfielLogValueChangeEntry {

	public $oldEmpty;
	public $newEmpty;

	public function __construct($property, $oldEmpty, $newEmpty) {
		parent::__construct($property);
		$this->oldEmpty = $oldEmpty;
		$this->newEmpty = $newEmpty;
	}

	/**
	 * @return string
	 */
	public function toHtml() {
		$old = $this->oldEmpty ? "" : "[GECENSUREERD]";
		$new = $this->newEmpty ? "" : "[GECENSUREERD]";
		return "($this->field) $old => $new";
	}
}