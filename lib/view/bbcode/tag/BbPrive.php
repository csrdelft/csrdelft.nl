<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\service\AccessService;
use Symfony\Component\Security\Core\Security;

/**
 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 *
 * @example [prive]Persoonsgegevens[/prive]
 * @example [prive=commissie:PubCie]Tekst[/prive]
 */
class BbPrive extends BbTag
{
	/**
	 * @var string
	 */
	private $permissie;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var AccessService
	 */
	private $accessService;

	public function __construct(Security $security, AccessService $accessService)
	{
		$this->security = $security;
		$this->accessService = $accessService;
	}

	public function isAllowed()
	{
		return $this->security->isGranted(
			$this->accessService->converteerPermissie($this->permissie)
		);
	}

	public static function getTagName()
	{
		return 'prive';
	}

	public function render()
	{
		return '<span class="bb-prive bb-tag-prive">' .
			$this->getContent() .
			'</span>';
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
		$this->permissie = $arguments['prive'] ?? 'ROLE_LOGGED_IN';
	}

	public function getPermissie()
	{
		return $this->permissie;
	}
}
