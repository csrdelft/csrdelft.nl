<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbOrderedList extends BbTag
{
	private $type;

	public static function getTagName()
	{
		return 'ol';
	}

	public function parse($arguments = [])
	{
		if (isset($arguments['ol'])) {
			$this->type = htmlspecialchars($arguments['ol']);
		}
		$this->readContent();
	}

	public function render()
	{
		if ($this->type) {
			return "<ol type=\"{$this->type}\">{$this->getContent()}</ol>";
		}
		return "<ol>{$this->getContent()}</ol>";
	}

	public function getType()
	{
		return $this->type;
	}
}
