<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 25-5-18
 * Time: 21:23
 */

namespace CsrDelft\model\entity\profiel;


class ProfielLogTextEntry extends AbstractProfielLogEntry {

	public $text;

	public function __construct($text) {
		$this->text = $text;
	}

	public function toHtml() {
		return htmlspecialchars($this->text);
	}


}