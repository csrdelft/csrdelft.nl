<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 25-5-18
 * Time: 21:12
 */

namespace CsrDelft\model\entity\profiel;


use CsrDelft\model\ProfielModel;

class ProfielUpdateLogGroup extends ProfielLogGroup {
	/**
	 * All changes in the entry
	 * @var AbstractProfielLogEntry[]
	 */
	public $entries;



	public function __construct($editor, $timestamp, $entries) {
		parent::_construct($editor, $timestamp);
		$this->entries = $entries;

	}

	/**
	 * @return string
	 */
	public function toHtml() {
		$changesHtml = [];
		foreach ($this->entries as $change) {
			$changesHtml[] = "<div class='change'>{$change->toHtml()}</div>";
		}
		//return json_encode($this, JSON_PRETTY_PRINT);
		return "<div class='ProfielLogEntry'>
			<div class='metadata'>Gewijzigd door ".ProfielModel::getLink($this->editor, 'civitas')." ".($this->timestamp === null ? "?" : reldate($this->timestamp->format('Y-m-d H:i:s')))."</div>
			".implode($changesHtml)."
			</div>";
	}
}