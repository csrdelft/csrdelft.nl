<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;

class BbParagraph extends BbTag
{
	public function parse($arguments = []): void
	{
		$this->readContent();
	}

	public function render(): string
	{
		return "<p>{$this->getContent()}</p>";
	}

	public static function getTagName()
	{
		return 'p';
	}
}
