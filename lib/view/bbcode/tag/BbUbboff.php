<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

/**
 * UBB off
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [ubboff]Not parsed[/ubboff]
 * @example [tekst]Not parsed[/tekst]
 */
class BbUbboff extends BbTag
{
	public static function getTagName(): array
	{
		return ['ubboff', 'tekst'];
	}

	public function render(): string
	{
		return $this->getContent();
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []): void
	{
		$this->parser->bb_mode = false;
		$this->readContent();
		$this->parser->bb_mode = true;
	}

	protected function getStoppers(): array
	{
		// De [/] tag werkt niet hier
		return ['[/tekst]', '[/uboff]'];
	}
}
