<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\service\forum\ForumDelenService;
use Exception;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class BbForum extends BbTag
{
	public $num = 3;
	/**
	 * @var ForumDeel
	 */
	private $deel;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var ForumDelenRepository
	 */
	private $forumDelenRepository;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var ForumDelenService
	 */
	private $forumDelenService;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		ForumDradenRepository $forumDradenRepository,
		ForumDelenRepository $forumDelenRepository,
		ForumDelenService $forumDelenService,
		Security $security,
		Environment $twig
	) {
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDelenRepository = $forumDelenRepository;
		$this->twig = $twig;
		$this->forumDelenService = $forumDelenService;
		$this->security = $security;
	}

	public static function getTagName()
	{
		return 'forum';
	}

	public function isAllowed(): bool
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
	public function render(): string
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
	public function parse($arguments = []): void
	{
		$this->id = $this->readMainArgument($arguments);
		if (isset($arguments['num'])) {
			$this->num = (int) $arguments['num'];
		}

		$this->forumDradenRepository->setAantalPerPagina($this->num);
		switch ($this->id) {
			case 'recent':
				$this->deel = $this->forumDelenService->getRecent();
				break;
			case 'belangrijk':
				$this->deel = $this->forumDelenService->getRecent(true);
				break;
			default:
				$this->deel = $this->forumDelenRepository->get($this->id);
				break;
		}
	}
}
