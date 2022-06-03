<?php


namespace CsrDelft\view\bbcode\prosemirror\embed;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbTwitter;
use InvalidArgumentException;

class NodeTwitter implements Node
{
    public static function getBbTagType()
    {
        return BbTwitter::class;
    }

    public static function getNodeType()
    {
        return 'twitter';
    }

    public function getData(BbNode $node)
    {
        if (!$node instanceof BbTwitter) {
            throw new InvalidArgumentException();
        }

        return [
            'attrs' => [
                'url' => $node->url
            ]
        ];
    }

    public function getTagAttributes($node)
    {
        return [
            'twitter' => $node->attrs->url
        ];
    }

    public function selfClosing()
    {
        return true;
    }
}
