<?php

namespace CsrDelft\model\entity\profiel;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\repository\ProfielRepository;

/**
 * ProfielUpdateLogGroup.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * LogGroup uit het legacy log die nog niet geparsed is.
 *
 */
class ProfielUpdateLogGroup extends ProfielLogGroup
{
	/**
	 * @param \CsrDelft\model\entity\profiel\AbstractProfielLogEntry[] $entries
	 */
	public function __construct(
		$editor,
		$timestamp /**
		 * All changes in the entry
		 * @var AbstractProfielLogEntry[]
		 */,
		public $entries
	) {
		parent::__construct($editor, $timestamp);
	}

	/**
	 * @return string
	 */
	public function toHtml()
	{
		$changesHtml = [];
		foreach ($this->entries as $change) {
			$changesHtml[] = "<div class='change'>{$change->toHtml()}</div>";
		}
		return "<div class='ProfielLogEntry'>
			<div class='metadata'>Gewijzigd door " .
			ProfielRepository::getLink($this->editor, 'civitas') .
			' ' .
			($this->timestamp === null
				? '?'
				: DateUtil::reldate($this->timestamp->format('Y-m-d H:i:s'))) .
			"</div>
			" .
			implode('', $changesHtml) .
			"
			</div>";
	}

	/**
	 * Censureer alle velden met gegeven naam
	 * @param $naam
	 * @return boolean Of er data gecensureerd is
	 */
	public function censureerVeld($naam): bool
	{
		$data_verwijderd = false;
		for ($i = 0; $i < sizeof($this->entries); $i++) {
			$gecensureerd = $this->entries[$i]->censureerVeld($naam);
			$data_verwijderd |= $gecensureerd !== $this->entries[$i];
			$this->entries[$i] = $gecensureerd;
		}
		return $data_verwijderd;
	}
}
