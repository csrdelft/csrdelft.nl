<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\Node;
use Psr\Container\ContainerInterface;

/**
 * Converteer een Prosemirror document naar BB code.
 *
 * Conversie Prosemirror->BB->Prosemirror zorgt altijd voor dezelfde Prosemirror objecten.
 *
 * Zorg ervoor dat alle Node & Mark types in het document beschikbaar zijn.
 *
 * @see Node Implementeer deze interface voor alle nodes.
 * @see Mark Implementeer deze interface voor alle marks.
 *
 * @package CsrDelft\view\bbcode
 */
class ProsemirrorToBb
{
	protected $document;
	/**
	 * Bevat @see Mark instances, met sleutel getMarkType
	 * @var ContainerInterface
	 */
	private $marksRegistry;
	/**
	 * Bevat @see Node instances, met sleutel getNodeType
	 * @var ContainerInterface
	 */
	private $nodesRegistry;

	public function __construct($marksRegistry, $nodesRegistry)
	{
		$this->marksRegistry = $marksRegistry;
		$this->nodesRegistry = $nodesRegistry;
	}

	public function convertToBb($value): string
	{
		$this->document($value);

		$bb = [];

		$content = is_array($this->document->content)
			? $this->document->content
			: [];

		foreach ($content as $node) {
			$bb[] = $this->convertNodeToBb($node);
		}

		return implode('', $bb);
	}

	private function document($value): static
	{
		if (is_string($value)) {
			$value = json_decode($value);
		} elseif (is_array($value)) {
			$value = json_decode(json_encode($value));
		}

		$this->document = $value;

		return $this;
	}

	private function convertNodeToBb($node): string
	{
		$bb = [];

		if (isset($node->marks)) {
			foreach ($node->marks as $mark) {
				/** @var Mark $markRenderClass */
				$markRenderClass = $this->marksRegistry->get($mark->type);
				if ($markRenderClass == null) {
					continue;
				}

				$tagName = $markRenderClass::getBbTagType()::getTagName();
				$bb[] = $this->renderOpeningTag(
					$tagName,
					$markRenderClass->getTagAttributes($mark)
				);
			}
		}

		if ($node->type != 'text') {
			/** @var Node $renderClass */
			$markRenderClass = $this->nodesRegistry->get($node->type);

			if ($markRenderClass != null) {
				$tagName = $markRenderClass->getBbTagType()::getTagName();
				$bb[] = $this->renderOpeningTag(
					$tagName,
					$markRenderClass->getTagAttributes($node)
				);
			}
		}

		if (isset($node->content)) {
			foreach ($node->content as $nestedNode) {
				$bb[] = $this->convertNodeToBb($nestedNode);
			}
		} elseif (isset($node->text)) {
			$bb[] = $node->text;
		}

		/** @var Node $nodeRenderClass */
		$nodeRenderClass = $this->nodesRegistry->get($node->type);

		if ($nodeRenderClass && !$nodeRenderClass->selfClosing()) {
			$tagName = $nodeRenderClass->getBbTagType()::getTagName();
			$bb[] = $this->renderClosingTag($tagName);
		}

		if (isset($node->marks)) {
			foreach (array_reverse($node->marks) as $mark) {
				/** @var Mark $markRenderClass */
				$markRenderClass = $this->marksRegistry->get($mark->type);
				$tagName = $markRenderClass->getBbTagType()::getTagName();
				$bb[] = $this->renderClosingTag($tagName);
			}
		}

		return implode('', $bb);
	}

	private function renderOpeningTag($tagName, $tagAttributes): string
	{
		// A bb tag can define multiple allowed tag names, we choose the first.
		if (is_array($tagName)) {
			$tagName = $tagName[0];
		}

		if (empty($tagAttributes)) {
			return "[{$tagName}]";
		}

		$attrs = '';
		$content = '';
		foreach ($tagAttributes as $attribute => $value) {
			if (!$value) {
				continue;
			}

			if ($attribute === 0) {
				$content = $value;
				continue;
			}

			if ($attribute == $tagName) {
				$tagName = "{$tagName}={$value}";
			} else {
				$attrs .= " {$attribute}={$value}";
			}
		}

		return "[{$tagName}{$attrs}]{$content}";
	}

	private function renderClosingTag($tagName): string
	{
		// A bb tag can define multiple allowed tag names, we choose the first.
		if (is_array($tagName)) {
			$tagName = $tagName[0];
		}
		return "[/{$tagName}]";
	}
}
