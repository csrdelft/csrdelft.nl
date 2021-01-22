<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;

interface Node
{
	/**
	 * @return BbTag|BbString
	 */
	public function getBbTagType();

	/**
	 * @return string
	 */
	public function getNodeType();

	/**
	 * @param BbNode $node
	 * @return mixed
	 */
	public function getData(BbNode $node);

	/**
	 * @param $node
	 * @return string[]
	 */
	public function getTagAttributes($node);

	/**
	 * @return bool
	 */
	public function selfClosing();
}
