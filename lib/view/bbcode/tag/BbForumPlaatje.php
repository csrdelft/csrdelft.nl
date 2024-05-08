<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\entity\ForumPlaatje;
use CsrDelft\repository\ForumPlaatjeRepository;
use Symfony\Bundle\SecurityBundle\Security;

class BbForumPlaatje extends BbImg
{
	/**
	 * @var ForumPlaatje
	 */
	private $plaatje;
	/**
	 * @var ForumPlaatjeRepository
	 */
	private $forumPlaatjeRepository;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		Security $security,
		ForumPlaatjeRepository $forumPlaatjeRepository
	) {
		$this->forumPlaatjeRepository = $forumPlaatjeRepository;
		$this->security = $security;
	}

	public static function getTagName(): string
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

	public function getLinkUrl(): string
	{
		return $this->plaatje->getUrl(false);
	}

	public function getSourceUrl(): string
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
