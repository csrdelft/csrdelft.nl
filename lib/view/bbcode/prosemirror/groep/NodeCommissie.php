<?php


namespace CsrDelft\view\bbcode\prosemirror\groep;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbCommissie;
use InvalidArgumentException;

class NodeCommissie implements Node
{
    public static function getBbTagType()
    {
        return BbCommissie::class;
    }

    public static function getNodeType()
    {
        return 'commissie';
    }

    public function getData(BbNode $node)
    {
        if (!$node instanceof BbCommissie) {
            throw new InvalidArgumentException();
        }

        return [
            'attrs' => ['id' => $node->getId()]
        ];
    }

    public function getTagAttributes($node)
    {
        return [
            'commissie' => $node->attrs->id,
        ];
    }

    public function selfClosing()
    {
        return true;
    }
}
