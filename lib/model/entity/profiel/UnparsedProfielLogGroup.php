<?php

namespace CsrDelft\model\entity\profiel;

use CsrDelft\view\bbcode\CsrBB;

/**
 * UnparsedProfielLogGroup.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * LogGroup uit het legacy log die nog niet geparsed is.
 *
 */
class UnparsedProfielLogGroup extends ProfielLogGroup
{

	/**
	 * BB-code uit het oude log.
	 * @var string content
	 */
	public $content;

	/**
	 * UnparsedProfielLogEntry constructor.
	 * @param $content
	 */
	public function __construct($content)
	{
		parent::__construct(null, null);
		$this->content = $content;
	}

	public function toHtml()
	{
		return "<div class='ProfielLogEntry'>" . CsrBB::parse($this->content) . "</div>";
	}
}
