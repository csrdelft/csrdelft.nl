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
	/**
	 * @return string[]
	 *
	 * @psalm-return list{'ubboff', 'tekst'}
	 */
	public static function getTagName()
	{
		return ['ubboff', 'tekst'];
	}

	public function render()
	{
		return $this->getContent();
	}

	/**
	 * @param array $arguments
	 *
	 * @return void
	 */
	public function parse($arguments = [])
	{
		$this->parser->bb_mode = false;
		$this->readContent();
		$this->parser->bb_mode = true;
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return list{'[/tekst]', '[/uboff]'}
	 */
	protected function getStoppers()
	{
		// De [/] tag werkt niet hier
		return ['[/tekst]', '[/uboff]'];
	}
}
