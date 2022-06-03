<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbQuote;

class NodeQuote implements Node
{
    public static function getBbTagType()
    {
        return BbQuote::class;
    }

    public static function getNodeType()
    {
        return 'blockquote';
    }

    public function getData(BbNode $node)
    {
        return [];
    }

    public function getTagAttributes($node)
    {
        return [];
    }

    public function selfClosing()
    {
        return false;
    }
}
