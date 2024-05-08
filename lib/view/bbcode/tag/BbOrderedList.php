<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbOrderedList extends BbTag
{
	private $type;
	private $order;

	public static function getTagName(): string
	{
		return 'ol';
	}

	public function parse($arguments = []): void
	{
		if (isset($arguments['ol'])) {
			$this->type = htmlspecialchars($arguments['ol']);
		}
		if (isset($arguments['order'])) {
			$this->order = htmlspecialchars($arguments['order']);
		}
		$this->readContent();
	}

	public function render(): string
	{
		$attrs = '';
		if ($this->order) {
			$attrs .= " start=\"{$this->order}\"";
		}

		if ($this->type) {
			$attrs .= " type=\"{$this->type}\"";
		}

		return "<ol$attrs>{$this->getContent()}</ol>";
	}

	public function getType()
	{
		return $this->type;
	}

	public function getOrder()
	{
		return $this->order;
	}
}
