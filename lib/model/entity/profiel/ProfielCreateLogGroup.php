<?php

namespace CsrDelft\model\entity\profiel;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\repository\ProfielRepository;

/**
 * ProfielCreateLogGroup.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * LogGroup gegenereerd bij aanmaken profiel.
 *
 */
class ProfielCreateLogGroup extends ProfielLogGroup
{
	public function __construct($editor, $timestamp)
	{
		parent::__construct($editor, $timestamp);
	}
	public function toHtml()
	{
		return "<div class='ProfielLogEntry'>Aangemaakt door " .
			ProfielRepository::getLink($this->editor) .
			($this->timestamp === null
				? '?'
				: DateUtil::reldate($this->timestamp->format('Y-m-d H:i:s'))) .
			'</div>';
	}
}
