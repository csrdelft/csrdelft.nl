<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\bbcode\BbHelper;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [lid=0436]
 * @example [lid]0436[/lid]
 */
class BbLid extends BbTag
{
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var string
	 */
	public $uid;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		Security $security,
		ProfielRepository $profielRepository
	) {
		$this->profielRepository = $profielRepository;
		$this->security = $security;
	}

	public static function getTagName()
	{
		return 'lid';
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LEDEN_READ') ||
			$this->security->isGranted('ROLE_OUDLEDEN_READ');
	}

	public function renderLight()
	{
		$profiel = $this->getProfiel();
		return BbHelper::lightLinkInline(
			$this->env,
			'lid',
			'/profiel/' . $profiel->uid,
			$profiel->getNaam('user')
		);
	}

	/**
	 * @return Profiel
	 * @throws BbException
	 */
	public function getProfiel()
	{
		$profiel = $this->profielRepository->find($this->uid);

		if (!$profiel) {
			throw new BbException(
				'[lid] ' . htmlspecialchars($this->uid) . '] &notin; db.'
			);
		}

		return $profiel;
	}

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render()
	{
		$profiel = $this->getProfiel();
		return $profiel->getLink('user');
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->uid = $this->readMainArgument($arguments);
	}
}
