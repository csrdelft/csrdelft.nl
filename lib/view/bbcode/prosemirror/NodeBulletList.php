<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbList;
use CsrDelft\bb\tag\BbNode;

class NodeBulletList implements Node
{
    public static function getBbTagType()
    {
        return BbList::class;
    }

    public static function getNodeType()
    {
        return 'bullet_list';
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
