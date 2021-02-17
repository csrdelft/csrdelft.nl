<?php


namespace CsrDelft\view\bbcode;


use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\Node;
use Psr\Container\ContainerInterface;

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

	public function render($value)
	{
		$this->document($value);

		$html = [];

		$content = is_array($this->document->content) ? $this->document->content : [];

		foreach ($content as $node) {
			$html[] = $this->renderNode($node);
		}

		return implode("", $html);
	}

	public function document($value)
	{
		if (is_string($value)) {
			$value = json_decode($value);
		} elseif (is_array($value)) {
			$value = json_decode(json_encode($value));
		}

		$this->document = $value;

		return $this;
	}

	private function renderNode($node)
	{
		$html = [];

		if (isset($node->marks)) {
			foreach ($node->marks as $mark) {
				/** @var Mark $markRenderClass */
				$markRenderClass = $this->marksRegistry->get($mark->type);
				if ($markRenderClass == null) {
					continue;
				}

				$tagName = $markRenderClass::getBbTagType()::getTagName();
				$html[] = $this->renderOpeningTag($tagName, $markRenderClass->getTagAttributes($mark));
			}
		}

		if ($node->type != "text") {
			/** @var Node $renderClass */
			$markRenderClass = $this->nodesRegistry->get($node->type);

			if ($markRenderClass != null) {
				$tagName = $markRenderClass->getBbTagType()::getTagName();
				$html[] = $this->renderOpeningTag($tagName, $markRenderClass->getTagAttributes($node));
			}
		}

		if (isset($node->content)) {
			foreach ($node->content as $nestedNode) {
				$html[] = $this->renderNode($nestedNode);
			}
		} elseif (isset($node->text)) {
			$html[] = htmlentities($node->text, ENT_QUOTES);
		}

		/** @var Node $nodeRenderClass */
		$nodeRenderClass = $this->nodesRegistry->get($node->type);

		if ($nodeRenderClass && !$nodeRenderClass->selfClosing()) {
			$tagName = $nodeRenderClass->getBbTagType()::getTagName();
			$html[] = $this->renderClosingTag($tagName);
		}

		if (isset($node->marks)) {
			foreach (array_reverse($node->marks) as $mark) {
				/** @var Mark $markRenderClass */
				$markRenderClass = $this->marksRegistry->get($mark->type);
				$tagName = $markRenderClass->getBbTagType()::getTagName();
				$html[] = $this->renderClosingTag($tagName);
			}
		}

		return implode("", $html);
	}

	private function renderOpeningTag($tagName, $tagAttributes)
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

	private function renderClosingTag($tagName)
	{
		// A bb tag can define multiple allowed tag names, we choose the first.
		if (is_array($tagName)) {
			$tagName = $tagName[0];
		}
		return "[/{$tagName}]";
	}
}

