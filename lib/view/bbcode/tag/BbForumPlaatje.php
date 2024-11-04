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

	/**
	 * @return string
	 *
	 * @psalm-return 'plaatje'
	 */
	public static function getTagName()
	{
		return 'plaatje';
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LOGGED_IN');
	}

	public function getKey(): string
	{
		return $this->plaatje->access_key;
	}

	/**
	 * @return string
	 */
	public function getLinkUrl()
	{
		return $this->plaatje->getUrl(false);
	}

	/**
	 * @return string
	 */
	public function getSourceUrl()
	{
		return $this->plaatje->getUrl(true);
	}

	/**
	 * @return string
	 *
	 * @psalm-return ' 📷 '
	 */
	public function renderPreview()
	{
		return ' 📷 ';
	}

	/**
	 * @return string
	 */
	public function renderPlain()
	{
		return 'Plaatje (' . $this->getLinkUrl() . ')';
	}

	/**
	 * @param array $arguments
	 *
	 * @throws BbException
	 *
	 * @return void
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
