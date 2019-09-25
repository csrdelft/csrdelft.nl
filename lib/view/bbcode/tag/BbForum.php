<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\security\LoginModel;

class BbForum extends BbTag {

	public function getTagName() {
		return 'forum';
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = []) {
		if (!LoginModel::mag(P_LOGGED_IN)) {
			return '';
		}

		$deel = $this->getArgument($arguments);
		$num = 3;

		if (isset($arguments['num'])) {
			$num = (int) $arguments['num'];
		}

		ForumDradenModel::instance()->setAantalPerPagina($num);

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
}
