<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use stdClass;

interface Node
{
    /**
     * Referentie naar type in Bb.
     *
     * @return BbTag|BbString
     */
    public static function getBbTagType();

    /**
     * Referentie naar type in Prosemirror schema.
     *
     * @return string
     */
    public static function getNodeType();

    /**
     * Prosemirror definitie.
     *
     * @param BbNode $node
     * @return mixed
     */
    public function getData(BbNode $node);

    /**
     * Bb attributes.
     *
     * @param $node stdClass Prosemirror definitie.
     * @return string[]
     */
    public function getTagAttributes($node);

    /**
     * Moet er een close tag gerendered worden?
     *
     * @return bool
     */
    public function selfClosing();
}
