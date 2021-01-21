<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;

interface Mark
{
	public function getBbTagType();
	public function getData(BbNode $node);
}
