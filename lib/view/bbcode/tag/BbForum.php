<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbTag;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\service\security\LoginService;
use Exception;

class BbForum extends BbTag {
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

	public function __construct(ForumDradenRepository $forumDradenRepository, ForumDelenRepository $forumDelenRepository) {
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDelenRepository = $forumDelenRepository;
	}

	public static function getTagName() {
		return 'forum';
	}

	public function isAllowed() {
		if ($this->content == 'recent' || $this->content == 'belangrijk') {
			return LoginService::mag(P_LOGGED_IN);
		}

		return $this->deel->magLezen();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function render() {
		if (!LoginService::mag(P_LOGGED_IN)) {
			return 'Geen toegang';
		}

		return view('forum.bb', [
			'deel' => $this->deel,
			'id' => $this->content,
		])->getHtml();
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []) {
		$this->readMainArgument($arguments);
		if (isset($arguments['num'])) {
			$this->num = (int)$arguments['num'];
		}

		$this->forumDradenRepository->setAantalPerPagina($this->num);
		switch ($this->content) {
			case 'recent':
				$this->deel = $this->forumDelenRepository->getRecent();
				break;
			case 'belangrijk':
				$this->deel = $this->forumDelenRepository->getRecent(true);
				break;
			default:
				$this->deel = $this->forumDelenRepository->get($this->content);
				break;
		}
	}
}
