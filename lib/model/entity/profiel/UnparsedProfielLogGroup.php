<?php

namespace CsrDelft\model\entity\profiel;
use CsrDelft\view\bbcode\CsrBB;

/**
 * Created by PhpStorm.
 * User: sander
 * Date: 11-7-18
 * Time: 10:04
 */

class UnparsedProfielLogGroup extends ProfielLogGroup {

	public $content;
	/**
	 * UnparsedProfielLogEntry constructor.
	 * @param $content
	 */
	public function __construct($content) {
		parent::_construct(null, null);
		$this->content = $content;
	}

	public function toHtml() {
		return "<div class='ProfielLogEntry'>".CsrBB::parse($this->content)."</div>";
	}
}