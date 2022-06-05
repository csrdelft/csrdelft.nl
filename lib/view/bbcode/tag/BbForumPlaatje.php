<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\entity\ForumPlaatje;
use CsrDelft\repository\ForumPlaatjeRepository;

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

	public function __construct(ForumPlaatjeRepository $forumPlaatjeRepository)
	{
		$this->forumPlaatjeRepository = $forumPlaatjeRepository;
	}

	public static function getTagName()
	{
		return 'plaatje';
	}

	public function isAllowed()
	{
		return mag('P_LOGGED_IN');
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
		$plaatje = $this->forumPlaatjeRepository->getByKey($key);
		if (!$plaatje) {
			throw new BbException('Plaatje bestaat niet');
		}
		$this->plaatje = $plaatje;
		$this->arguments = $arguments;
	}
}
