<?php


namespace CsrDelft\view\bbcode\prosemirror\groep;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbVerticale;
use InvalidArgumentException;

class NodeVerticale implements Node
{
    public static function getBbTagType()
    {
        return BbVerticale::class;
    }

    public static function getNodeType()
    {
        return 'verticale';
    }

    public function getData(BbNode $node)
    {
        if (!$node instanceof BbVerticale) {
            throw new InvalidArgumentException();
        }

        return [
            'attrs' => ['id' => $node->getLetter()]
        ];
    }

    public function getTagAttributes($node)
    {
        return [
            'verticale' => $node->attrs->id,
        ];
    }

    public function selfClosing()
    {
        return true;
    }
}
