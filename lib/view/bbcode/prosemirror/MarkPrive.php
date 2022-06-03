<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbPrive;
use InvalidArgumentException;

class MarkPrive implements Mark
{
    public static function getBbTagType()
    {
        return BbPrive::class;
    }

    public static function getMarkType()
    {
        return 'prive';
    }

    public function getTagAttributes($mark)
    {
        return [
            'prive' => $mark->attrs->prive,
        ];
    }

    public function getData(BbNode $node)
    {
        if (!$node instanceof BbPrive) {
            throw new InvalidArgumentException();
        }

        return [
            'attrs' => ['prive' => $node->getPermissie()]
        ];
    }
}
