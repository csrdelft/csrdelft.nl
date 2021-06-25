<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;

class BbParagraph extends BbTag
{
	public function parse($arguments = [])
	{
		$this->readContent();
	}

	public function render()
	{
		return "<p>{$this->getContent()}</p>";
	}

	public static function getTagName()
	{
		return 'p';
	}
}
