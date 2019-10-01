<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbTag;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\security\LoginModel;
use Exception;

class BbForum extends BbTag {
	public $num = 3;
	/**
	 * @var ForumDeel
	 */
	private $deel;

	public static function getTagName() {
		return 'forum';
	}

	public function isAllowed() {
		if ($this->content == 'recent' || $this->content == 'belangrijk') {
			return LoginModel::mag(P_LOGGED_IN);
		}

		return $this->deel->magLezen();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function render() {
		if (!LoginModel::mag(P_LOGGED_IN)) {
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

		ForumDradenModel::instance()->setAantalPerPagina($this->num);
		switch ($this->content) {
			case 'recent':
				$this->deel = ForumDelenModel::instance()->getRecent();
				break;
			case 'belangrijk':
				$this->deel = ForumDelenModel::instance()->getRecent(true);
				break;
			default:
				$this->deel = ForumDelenModel::instance()->get($this->content);
				break;
		}
	}
}
