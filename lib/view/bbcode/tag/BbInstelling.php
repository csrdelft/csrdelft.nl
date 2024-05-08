<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Toont content als instelling een bepaalde waarde heeft, standaard 'ja';
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [instelling=maaltijdblokje module=voorpagina][maaltijd=next][/instelling]
 */
class BbInstelling extends BbTag
{
	private $module;
	private $testwaarde;
	private $instelling;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var LidInstellingenRepository
	 */
	private $lidInstellingenRepository;

	public function __construct(
		Security $security,
		LidInstellingenRepository $lidInstellingenRepository
	) {
		$this->security = $security;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LOGGED_IN');
	}

	public static function getTagName()
	{
		return 'instelling';
	}

	public function render()
	{
		if ($this->instelling == null) {
			return 'Geen instelling opgegeven';
		}
		try {
			if (
				$this->lidInstellingenRepository->getValue(
					$this->module,
					$this->instelling
				) == $this->testwaarde
			) {
				return $this->getContent();
			}
		} catch (CsrException $e) {
			return '[instelling]: ' . $e->getMessage();
		}

		return '';
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
		if (
			!array_key_exists('instelling', $arguments) ||
			!isset($arguments['instelling'])
		) {
			return;
		}
		if (
			!array_key_exists('module', $arguments) ||
			!isset($arguments['module'])
		) {
			// backwards compatibility
			$key = explode('_', $arguments['instelling'], 2);
			$this->module = $key[0];
			$this->instelling = $key[1];
		} else {
			$this->instelling = $arguments['instelling'];
			$this->module = $arguments['module'];
		}
		$this->testwaarde = $arguments['waarde'] ?? 'ja';
	}
}
