<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;

interface Node
{
	public function getBbTagType();
	public function getData(BbNode $node);
}
