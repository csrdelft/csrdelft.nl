<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\repository\groepen\VerticalenRepository;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Geeft een link naar de verticale.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [verticale]A[/verticale]
 * @example [verticale=A]
 */
class BbVerticale extends BbTag
{
	/**
	 * @var VerticalenRepository
	 */
	private $verticalenRepository;
	/**
	 * @var string
	 */
	private $letter;
	/**
	 * @var Security
	 */
	private $security;

	public function getLetter(): string
	{
		return $this->letter;
	}

	public function __construct(
		Security $security,
		VerticalenRepository $verticalenRepository
	) {
		$this->verticalenRepository = $verticalenRepository;
		$this->security = $security;
	}

	public static function getTagName(): string
	{
		return 'verticale';
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LOGGED_IN');
	}

	public function render()
	{
		try {
			$verticale = $this->verticalenRepository->get($this->letter);
			return '<a href="/verticalen#' .
				$verticale->letter .
				'">' .
				$verticale->naam .
				'</a>';
		} catch (CsrException $e) {
			return 'Verticale met letter=' .
				htmlspecialchars($this->letter) .
				' bestaat niet. <a href="/verticalen">Zoeken</a>';
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []): void
	{
		$this->letter = $this->readMainArgument($arguments);
	}
}
