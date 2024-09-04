<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\bb\BbEnv;
use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\Node;
use Psr\Container\ContainerInterface;

/**
 * Converteer BB code naar een Prosemirror object. Als een stuk bbcode door de bb parser heen komt wordt er ook
 * altijd een Prosemirror object van gemaakt. Er is geen garantie dat BB->Prosemirror->BB dezelfde BB code als aan
 * het begin oplevert.
 *
 * Zorg ervoor dat alle Node & Mark types in het document beschikbaar zijn.
 *
 * @see Node Implementeer deze interface voor alle nodes.
 * @see Mark Implementeer deze interface voor alle marks.
 * @package CsrDelft\view\bbcode
 */
class BbToProsemirror
{
	/**
	 * @var CsrBB
	 */
	private $csrBB;

	private $storedMarks = [];

	/**
	 * @param ContainerInterface $marksRegistry
	 * @param ContainerInterface $nodesRegistry
	 */
	public function __construct(
		/**
		 * Bevat @see Mark instances, met sleutel getBbTagType
		 */
		private $marksRegistry,
		/**
		 * Bevat @see Node instances, met sleutel getBbTagType
		 */
		private $nodesRegistry,
		ContainerInterface $container
	) {
		$env = new BbEnv();
		$env->prosemirror = true;
		$this->csrBB = new CsrBB($container, $env);
	}

	/**
	 * Return een Fragment<EditorSchema>, deze heeft geen root, maar kan worden gebuikt om
	 * een node in te voegen in prosemirror. Bijvoorbeeld voor citeren.
	 *
	 * @param $bbCode
	 * @return array
	 */
	public function toProseMirrorFragment($bbCode)
	{
		return $this->nodeToProseMirror($this->csrBB->parseString($bbCode));
	}

	public function toProseMirror($bbCode)
	{
		$content = $this->toProseMirrorFragment($bbCode);

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
			if ($this->nodesRegistry->has($child::class)) {
				/** @var Node $class */
				$class = $this->nodesRegistry->get($child::class);
				$item = array_merge(
					['type' => $class::getNodeType()],
					$class->getData($child)
				);

				if ($item === null) {
					if (!empty($child->getChildren())) {
						$nodes = array_merge(
							$nodes,
							$this->nodeToProseMirror($child->getChildren())
						);
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

				array_push($nodes, $item);
			} elseif ($this->marksRegistry->has($child::class)) {
				/** @var Mark $class */
				$class = $this->marksRegistry->get($child::class);
				array_push(
					$this->storedMarks,
					array_merge(
						['type' => $class::getMarkType()],
						$class->getData($child)
					)
				);

				if (!empty($child->getChildren())) {
					$nodes = array_merge(
						$nodes,
						$this->nodeToProseMirror($child->getChildren())
					);
				}

				array_pop($this->storedMarks);
			} elseif (!empty($child->getChildren())) {
				$nodes = array_merge(
					$nodes,
					$this->nodeToProseMirror($child->getChildren())
				);
			}
		}

		return $nodes;
	}
}
