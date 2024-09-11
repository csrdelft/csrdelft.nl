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
	 * UnparsedProfielLogEntry constructor.
	 * @param $content
	 * @param string $content
	 */
	public function __construct(
		/**
		 * BB-code uit het oude log.
		 * @var string content
		 */ public $content
	) {
		parent::__construct(null, null);
	}

	public function toHtml()
	{
		return "<div class='ProfielLogEntry'>" .
			CsrBB::parse($this->content) .
			'</div>';
	}
}
