<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;

interface Mark
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
	public static function getMarkType();

	/**
	 * Bb attributes.
	 *
	 * @param $node \stdClass Prosemirror definitie.
	 * @return string[]
	 */
	public function getTagAttributes($mark);

	/**
	 * Prosemirror definitie.
	 *
	 * @param BbNode $node
	 * @return mixed
	 */
	public function getData(BbNode $node);
}
