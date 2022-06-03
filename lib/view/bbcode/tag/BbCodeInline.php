<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbCodeInline extends BbTag
{
    public function parse($arguments = [])
    {
        $this->readContent();
    }

    public function render()
    {
        return "<code>{$this->getContent()}</code>";
    }

    public static function getTagName()
    {
        return 'c';
    }
}
