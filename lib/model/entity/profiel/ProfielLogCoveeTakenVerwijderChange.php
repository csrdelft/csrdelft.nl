<?php

namespace CsrDelft\model\entity\profiel;

/**
 * ProfielLogCorveeTakenVerwijderChange.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * Log voor verwijderen van corveetaken.
 *
 */
class ProfielLogCoveeTakenVerwijderChange extends AbstractProfielLogChangeEntry
{
	/**
	 * @param string[] $corveetaken
	 */
	public function __construct(
		/**
		 * @var string[]
		 */
		public $corveetaken
	) {
	}

	public function toHtml()
	{
		return 'Verwijder corveetaken:' . implode(',', $this->corveetaken);
	}
}
