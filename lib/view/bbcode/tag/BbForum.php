<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\service\forum\ForumDelenService;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class BbForum extends BbTag
{
	public $num = 3;
	/**
	 * @var ForumDeel
	 */
	private $deel;
	/**
	 * @var string
	 */
	private $id;

	public function __construct(
		private readonly ForumDradenRepository $forumDradenRepository,
		private readonly ForumDelenRepository $forumDelenRepository,
		private readonly ForumDelenService $forumDelenService,
		private readonly Security $security,
		private readonly Environment $twig
	) {
	}

	public static function getTagName()
	{
		return 'forum';
	}

	public function isAllowed()
	{
		if ($this->id == 'recent' || $this->id == 'belangrijk') {
			return $this->security->isGranted('ROLE_LOGGED_IN');
		}

		return $this->deel->magLezen();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function render()
	{
		if (!$this->security->isGranted('ROLE_LOGGED_IN')) {
			return 'Geen toegang';
		}

		return $this->twig->render('forum/bb.html.twig', [
			'deel' => $this->deel,
			'id' => $this->id,
		]);
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->id = $this->readMainArgument($arguments);
		if (isset($arguments['num'])) {
			$this->num = (int) $arguments['num'];
		}

		$this->forumDradenRepository->setAantalPerPagina($this->num);
		$this->deel = match ($this->id) {
			'recent' => $this->forumDelenService->getRecent(),
			'belangrijk' => $this->forumDelenService->getRecent(true),
			default => $this->forumDelenRepository->get($this->id),
		};
	}
}
