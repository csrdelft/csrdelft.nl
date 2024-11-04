<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbCodeInline extends BbTag
{
	/**
	 * @return void
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
	}

	/**
	 * @return string
	 */
	public function render()
	{
		return "<code>{$this->getContent()}</code>";
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'c'
	 */
	public static function getTagName()
	{
		return 'c';
	}
}
