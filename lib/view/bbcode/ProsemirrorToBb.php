<?php


namespace CsrDelft\view\bbcode;


use CsrDelft\bb\internal\BbString;
use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\MarkBold;
use CsrDelft\view\bbcode\prosemirror\MarkItalic;
use CsrDelft\view\bbcode\prosemirror\MarkLink;
use CsrDelft\view\bbcode\prosemirror\MarkUnderline;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\prosemirror\NodeDocument;
use CsrDelft\view\bbcode\prosemirror\NodeImage;
use CsrDelft\view\bbcode\prosemirror\NodeString;
use CsrDelft\view\bbcode\prosemirror\NodeVerklapper;

class ProsemirrorToBb
{
	protected $document;

	const NODES = [
		NodeDocument::class,
		NodeImage::class,
		NodeString::class,
		NodeVerklapper::class
	];

	const MARKS = [
		MarkUnderline::class,
		MarkBold::class,
		MarkItalic::class,
		MarkLink::class,
	];

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
				foreach (self::MARKS as $class) {
					/** @var Mark $renderClass */
					$renderClass = new $class($mark);

					if ($renderClass->getMarkType() == $mark->type) {
						$tagName = $renderClass->getBbTagType()::getTagName();
						$html[] = $this->renderOpeningTag($tagName, $renderClass->getTagAttributes($mark));
					}
				}
			}
		}

		if ($node->type != "text") {
			foreach (self::NODES as $class) {
				/** @var Node $renderClass */
				$renderClass = new $class($node);

				if ($renderClass->getNodeType() == $node->type) {
					$tagName = $renderClass->getBbTagType()::getTagName();
					$html[] = $this->renderOpeningTag($tagName, $renderClass->getTagAttributes($node));
					break;
				}
			}
		}

		if (isset($node->content)) {
			foreach ($node->content as $nestedNode) {
				$html[] = $this->renderNode($nestedNode);
			}
		} elseif (isset($node->text)) {
			$html[] = htmlentities($node->text, ENT_QUOTES);
		}

		foreach (self::NODES as $class) {
			$renderClass = new $class($node);

			if ($renderClass->selfClosing()) {
				continue;
			}

			if ($renderClass->getNodeType() == $node->type) {
				$tagName = $renderClass->getBbTagType()::getTagName();
				$html[] = $this->renderClosingTag($tagName);
			}
		}

		if (isset($node->marks)) {
			foreach (array_reverse($node->marks) as $mark) {
				foreach (self::MARKS as $class) {
					$renderClass = new $class($mark);

					if ($renderClass->getMarkType() == $mark->type) {
						$tagName = $renderClass->getBbTagType()::getTagName();
						$html[] = $this->renderClosingTag($tagName);
					}
				}
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
		foreach ($tagAttributes as $attribute => $value) {
			if ($attribute == $tagName) {
				$tagName = "{$tagName}={$value}";
			} else {
				$attrs .= " {$attribute}={$value}";
			}
		}

		return "[{$tagName}{$attrs}]";
	}

	private function renderClosingTag($tagName)
	{
		// A bb tag can define multiple allowed tag names, we choose the first.
		if (is_array($tagName)) {
			$tagName = $tagName[0];
		}
		return "[/{$tagName}]";
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
}

