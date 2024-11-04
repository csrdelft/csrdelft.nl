<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbOrderedList extends BbTag
{
	private $type;
	private $order;

	/**
	 * @return string
	 *
	 * @psalm-return 'ol'
	 */
	public static function getTagName()
	{
		return 'ol';
	}

	/**
	 * @return void
	 */
	public function parse($arguments = [])
	{
		if (isset($arguments['ol'])) {
			$this->type = htmlspecialchars((string) $arguments['ol']);
		}
		if (isset($arguments['order'])) {
			$this->order = htmlspecialchars((string) $arguments['order']);
		}
		$this->readContent();
	}

	/**
	 * @return string
	 */
	public function render()
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

	public function getOrder()
	{
		return $this->order;
	}
}
