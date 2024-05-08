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

	public static function getTagName()
	{
		return ['taal'];
	}

	public function parse($arguments = [])
	{
		$this->taal = $arguments['taal'];
		$this->readContent();
	}

	public function render()
	{
		if ($this->requestStack->getCurrentRequest()->getLocale() == $this->taal) {
			return $this->getContent();
		} else {
			return '';
		}
	}
}
