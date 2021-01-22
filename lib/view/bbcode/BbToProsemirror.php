<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\MarkBold;
use CsrDelft\view\bbcode\prosemirror\MarkCode;
use CsrDelft\view\bbcode\prosemirror\MarkItalic;
use CsrDelft\view\bbcode\prosemirror\MarkLink;
use CsrDelft\view\bbcode\prosemirror\MarkUnderline;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\prosemirror\NodeCodeBlock;
use CsrDelft\view\bbcode\prosemirror\NodeDocument;
use CsrDelft\view\bbcode\prosemirror\NodeHeader;
use CsrDelft\view\bbcode\prosemirror\NodeHorizontalRule;
use CsrDelft\view\bbcode\prosemirror\NodeImage;
use CsrDelft\view\bbcode\prosemirror\NodeParagraph;
use CsrDelft\view\bbcode\prosemirror\NodeString;
use CsrDelft\view\bbcode\prosemirror\NodeVerklapper;

class BbToProsemirror
{
	public const MARKS = [
		MarkBold::class,
		MarkUnderline::class,
		MarkItalic::class,
		MarkCode::class,
		MarkLink::class,
	];

	public const NODES = [
		NodeDocument::class,
		NodeImage::class,
		NodeVerklapper::class,
		NodeString::class,
		NodeHeader::class,
		NodeHorizontalRule::class,
		NodeCodeBlock::class,
		NodeParagraph::class,
	];
	/**
	 * @var CsrBB
	 */
	private $csrBB;

	private $storedMarks = [];

	public function __construct(CsrBB $csrBB)
	{
		$this->csrBB = $csrBB;
	}

	public function toProseMirror($bbCode) {
		$nodes = $this->csrBB->parseString($bbCode);

		$content = $this->nodeToProseMirror($nodes);

		return [
			'type' => 'doc',
			'content' => $content,
		];
	}

	private function nodeToProseMirror($children) {
		$nodes = [];

		foreach ($children as $child) {
			if ($class = $this->findNode(get_class($child))) {
				$item = $class->getData($child);

				if ($item === null) {
					if (!empty($child->getChildren())) {
						$nodes = array_merge($nodes, $this->nodeToProseMirror($child->getChildren()));
					}
					continue;
				}

				if (!empty($child->getChildren())) {
					$item = array_merge($item, [
						'content' => $this->nodeToProseMirror($child->getChildren()),
					]);
				}

				if (count($this->storedMarks)) {
					$item = array_merge($item, [
						'marks' => $this->storedMarks,
					]);
				}

//				if ($class->wrapper) {
//					$item['content'] = [
//						array_merge($class->wrapper, [
//							'content' => @$item['content'] ?: [],
//						]),
//					];
//				}

				array_push($nodes, $item);
			} elseif ($class = $this->findMark(get_class($child))) {
				array_push($this->storedMarks, $class->getData($child));

				if (!empty($child->getChildren())) {
					$nodes = array_merge($nodes, $this->nodeToProseMirror($child->getChildren()));
				}

				array_pop($this->storedMarks);
			} elseif (!empty($child->getChildren())) {
				$nodes = array_merge($nodes, $this->nodeToProseMirror($child->getChildren()));
			}
		}

		return $nodes;
	}

	/**
	 * @param $class
	 * @return Mark|null
	 */
	private function findMark($class) {
		foreach (self::MARKS as $mark) {
			/** @var Mark $instance */
			$instance = new $mark();
			if ($instance->getBbTagType() == $class) {
				return $instance;
			}
		}

		return null;
	}

	/**
	 * @param $class
	 * @return Node|null
	 */
	private function findNode($class) {
		foreach(self::NODES as $node) {
			/** @var Node $instance */
			$instance = new $node();
			if ($instance->getBbTagType() == $class) {
				return $instance;
			}
		}

		return null;
	}
}
