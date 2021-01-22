<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;

interface Mark
{
	/**
	 * @return BbTag|BbString
	 */
	public function getBbTagType();

	/**
	 * @return string
	 */
	public function getMarkType();

	/**
	 * @param $mark
	 * @return mixed
	 */
	public function getTagAttributes($mark);

	/**
	 * @param BbNode $node
	 * @return mixed
	 */
	public function getData(BbNode $node);
}
