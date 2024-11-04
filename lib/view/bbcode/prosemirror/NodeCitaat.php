<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbCitaat;

class NodeCitaat implements Node
{
	/**
	 * @psalm-return BbCitaat::class
	 */
	public static function getBbTagType(): string
	{
		return BbCitaat::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'citaat'
	 */
	public static function getNodeType()
	{
		return 'citaat';
	}

	/**
	 * @return (string|string[])[][]
	 *
	 * @psalm-return array{attrs: array{van: array<string>|string, naam: array<string>|string, url: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbCitaat) {
			throw new \InvalidArgumentException();
		}

		if ($node->bron_profiel != null) {
			$van = $node->bron_profiel->uid;
			$naam = $node->bron_profiel->getNaam('user');
			$url = '/profiel/' . $node->bron_profiel->uid;
		} elseif ($node->bron_text != null) {
			$van = str_replace('_', ' ', $node->bron_text);
			$naam = $van;
			$url = $node->bron_url;
		} else {
			$van = '';
			$naam = '';
			$url = '';
		}

		return [
			'attrs' => [
				'van' => $van,
				'naam' => $naam,
				'url' => $url,
			],
		];
	}

	/**
	 * @return (mixed|string|string[])[]
	 *
	 * @psalm-return array{citaat: array<string>|string, url: mixed}
	 */
	public function getTagAttributes($node): array
	{
		return [
			'citaat' => str_replace(' ', '_', $node->attrs->van),
			'url' => $node->attrs->url,
		];
	}

	/**
	 * @return false
	 */
	public function selfClosing()
	{
		return false;
	}
}
