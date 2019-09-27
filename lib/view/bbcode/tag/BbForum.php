<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\security\LoginModel;

class BbForum extends BbTag {
	public $num = 3;
	public static function getTagName() {
		return 'forum';
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function render() {
		if (!LoginModel::mag(P_LOGGED_IN)) {
			return '';
		}
		$deel = $this->content;
		ForumDradenModel::instance()->setAantalPerPagina($this->num);

		if ($deel == 'recent') {
			$forumDeel = ForumDelenModel::instance()->getRecent();
		} else if ($deel == 'belangrijk') {
			$forumDeel = ForumDelenModel::instance()->getRecent(true);
		} else {
			$forumDeel = ForumDelenModel::instance()->get($deel);
			if (!$forumDeel->magLezen()) {
				throw new BbException('<div class="alert alert-warning">Mag dit forumdeel niet lezen</div>');
			}
		}

		return view('forum.bb', [
			'deel' => $forumDeel,
		])->getHtml();
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
		if (isset($arguments['num'])) {
			$this->num = (int) $arguments['num'];
		}
	}
}
