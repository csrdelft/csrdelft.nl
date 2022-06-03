<?php


namespace CsrDelft\view\bbcode\prosemirror\groep;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbBestuur;
use InvalidArgumentException;

class NodeBestuur implements Node
{
    public static function getBbTagType()
    {
        return BbBestuur::class;
    }

    public static function getNodeType()
    {
        return 'bestuur';
    }

    public function getData(BbNode $node)
    {
        if (!$node instanceof BbBestuur) {
            throw new InvalidArgumentException();
        }

        return [
            'attrs' => ['id' => $node->getId()]
        ];
    }

    public function getTagAttributes($node)
    {
        return [
            'bestuur' => $node->attrs->id,
        ];
    }

    public function selfClosing()
    {
        return true;
    }
}
