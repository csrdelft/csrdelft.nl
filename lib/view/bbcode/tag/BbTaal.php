<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Geef tekst weer gebasseerd op de huidige locale.
 *
 * @package CsrDelft\view\bbcode\tag
 */
class BbTaal extends BbTag
{
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var string
	 */
	private $taal;

	public function __construct(RequestStack $requestStack)
	{
		$this->requestStack = $requestStack;
	}

	public static function getTagName(): array
	{
		return ['taal'];
	}

	public function parse($arguments = []): void
	{
		$this->taal = $arguments['taal'];
		$this->readContent();
	}

	public function render(): string
	{
		if ($this->requestStack->getCurrentRequest()->getLocale() == $this->taal) {
			return $this->getContent();
		} else {
			return '';
		}
	}
}
