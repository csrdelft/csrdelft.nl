<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 11-7-18
 * Time: 15:59
 */

namespace CsrDelft\model\entity\profiel;


class ProfielLogCoveeTakenVerwijderChange extends AbstractProfielLogChangeEntry {
	/**
	 * @var string[]
	 */
	public $corveetaken = [];
	public function __construct($corveetaken) {
		$this->corveetaken = $corveetaken;
	}

	public function toHtml() {
		return "Verwijder corveetaken:". implode(",", $this->corveetaken);
	}
}