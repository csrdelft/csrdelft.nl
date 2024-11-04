<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbParagraph extends BbTag
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
		return "<p>{$this->getContent()}</p>";
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'p'
	 */
	public static function getTagName()
	{
		return 'p';
	}
}
