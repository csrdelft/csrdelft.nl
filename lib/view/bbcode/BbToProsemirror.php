<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\Node;
use Psr\Container\ContainerInterface;

class BbToProsemirror
{
	/**
	 * @var CsrBB
	 */
	private $csrBB;

	private $storedMarks = [];
	/**
	 * Bevat @see Mark instances, met sleutel getBbTagType
	 * @var ContainerInterface
	 */
	private $marksRegistry;
	/**
	 * Bevat @see Node instances, met sleutel getBbTagType
	 * @var ContainerInterface
	 */
	private $nodesRegistry;

	public function __construct($marksRegistry, $nodesRegistry, CsrBB $csrBB)
	{
		$this->csrBB = $csrBB;
		$this->marksRegistry = $marksRegistry;
		$this->nodesRegistry = $nodesRegistry;
	}

	public function toProseMirror($bbCode)
	{
		$nodes = $this->csrBB->parseString($bbCode);

		$content = $this->nodeToProseMirror($nodes);

		// Lege paragraph als er geen content is.
		if (empty($content)) {
			$content = [['type' => 'paragraph']];
		}

		return [
			'type' => 'doc',
			'content' => $content,
		];
	}

	private function nodeToProseMirror($children)
	{
		$nodes = [];

		foreach ($children as $child) {
			if ($this->nodesRegistry->has(get_class($child))) {
				$class = $this->nodesRegistry->get(get_class($child));
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
			} elseif ($this->marksRegistry->has(get_class($child))) {
				$class = $this->marksRegistry->get(get_class($child));
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
}
