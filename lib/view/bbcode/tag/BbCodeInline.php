<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;

class BbCodeInline extends BbTag
{
	public function parse($arguments = []): void
	{
		$this->readContent();
	}

	public function render(): string
	{
		return "<code>{$this->getContent()}</code>";
	}

	public static function getTagName()
	{
		return 'c';
	}
}
