<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

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

	public static function getTagName(): string
	{
		return 'c';
	}
}
