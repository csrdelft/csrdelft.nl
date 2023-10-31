<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbException;
use CsrDelft\entity\ForumPlaatje;
use CsrDelft\repository\ForumPlaatjeRepository;
use Symfony\Component\Security\Core\Security;

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

	public static function getTagName()
	{
		return 'plaatje';
	}

	public function isAllowed(): bool
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

	public function renderPreview(): string
	{
		return ' 📷 ';
	}

	public function renderPlain(): string
	{
		return 'Plaatje (' . $this->getLinkUrl() . ')';
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = []): void
	{
		$key = $this->readMainArgument($arguments);
		$this->plaatje = $this->forumPlaatjeRepository->getByKey($key);
		if (!$this->plaatje) {
			throw new BbException('Plaatje bestaat niet');
		}
		$this->arguments = $arguments;
	}
}
