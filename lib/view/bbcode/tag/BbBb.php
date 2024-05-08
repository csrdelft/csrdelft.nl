<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbBb extends BbTag
{
	public static function getTagName(): string
	{
		return 'bb';
	}

	public function parse($arguments = []): void
	{
		if (@$this->env->prosemirror) {
			$this->readContent([], false);
		} else {
			$this->readContent();
		}
	}

	public function render(): string
	{
		return $this->getContent();
	}
}
