<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 11-7-18
 * Time: 0:31
 */

namespace CsrDelft\model\entity\profiel;


use CsrDelft\model\ProfielModel;

class ProfielCreateLogGroup extends ProfielLogGroup {
	public function __construct($editor, $timestamp) {
		parent::_construct($editor, $timestamp);
	}
	public  function toHtml() {
		return "<div class='ProfielLogEntry'>Aangemaakt door ".ProfielModel::getLink($this->editor).($this->timestamp === null ? "?" : reldate($this->timestamp->format('Y-m-d H:i:s')))."</div>";
	}

}