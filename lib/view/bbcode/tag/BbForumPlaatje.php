<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\entity\ForumPlaatje;
use CsrDelft\repository\ForumPlaatjeRepository;
use Symfony\Component\Security\Core\Security;

class BbForumPlaatje extends BbImg
{
	/**
	 * @var ForumPlaatje
	 */
	private $plaatje;

	public function __construct(
		private readonly Security $security,
		private readonly ForumPlaatjeRepository $forumPlaatjeRepository
	) {
	}

	public static function getTagName()
	{
		return 'plaatje';
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LOGGED_IN');
	}

	public function getKey()
	{
		return $this->plaatje->access_key;
	}

	public function getLinkUrl()
	{
		return $this->plaatje->getUrl(false);
	}

	public function getSourceUrl()
	{
		return $this->plaatje->getUrl(true);
	}

	public function renderPreview()
	{
		return ' 📷 ';
	}

	public function renderPlain()
	{
		return 'Plaatje (' . $this->getLinkUrl() . ')';
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$key = $this->readMainArgument($arguments);
		$this->plaatje = $this->forumPlaatjeRepository->getByKey($key);
		if (!$this->plaatje) {
			throw new BbException('Plaatje bestaat niet');
		}
		$this->arguments = $arguments;
	}
}
