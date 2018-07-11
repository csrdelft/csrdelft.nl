<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 11-7-18
 * Time: 0:28
 */

namespace CsrDelft\model\entity\profiel;


abstract class ProfielLogGroup {
	/**
	 * UID of editor
	 * @var string
	 */
	public $editor;

	/**
	 * @var \DateTime
	 */
	public $timestamp;

	public function __construct($editor, $timestamp) {
		$this->editor = $editor;
		$this->timestamp = $timestamp;
	}

	public abstract function toHtml();

}