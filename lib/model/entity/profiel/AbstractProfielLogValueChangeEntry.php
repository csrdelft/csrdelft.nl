<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 28-5-18
 * Time: 21:05
 */

namespace CsrDelft\model\entity\profiel;


abstract class AbstractProfielLogValueChangeEntry extends AbstractProfielLogChangeEntry {

	/**
	 * @var string
	 */
	public $field;
}