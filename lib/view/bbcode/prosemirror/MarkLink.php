<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbUrl;
use InvalidArgumentException;

class MarkLink implements Mark
{
    public static function getBbTagType()
    {
        return BbUrl::class;
    }

    public static function getMarkType()
    {
        return 'link';
    }

    public function getTagAttributes($mark)
    {
        return [
            'url' => $mark->attrs->href
        ];
    }

    public function getData(BbNode $node)
    {
        if (!$node instanceof BbUrl) {
            throw new InvalidArgumentException();
        }

        return [
            'attrs' => [
                'href' => $node->url,
            ],
        ];
    }
}
