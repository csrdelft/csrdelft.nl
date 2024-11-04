<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbBb extends BbTag
{
	/**
	 * @return string
	 *
	 * @psalm-return 'bb'
	 */
	public static function getTagName()
	{
		return 'bb';
	}

	/**
	 * @return void
	 */
	public function parse($arguments = [])
	{
		if (@$this->env->prosemirror) {
			$this->readContent([], false);
		} else {
			$this->readContent();
		}
	}

	public function render()
	{
		return $this->getContent();
	}
}
