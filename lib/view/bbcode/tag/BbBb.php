<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;

class BbBb extends BbTag
{
	public static function getTagName()
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
