<?php

namespace CsrDelft\view\forum;

use CsrDelft\model\forum\ForumModel;
use CsrDelft\view\forum;

class ForumOverzichtView extends ForumView {

	public function __construct() {
		parent::__construct(ForumModel::instance()->getForumIndelingVoorLid(), 'Forum');
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/forum/recent">Recent</a>';
	}

	public function view() {
		$this->smarty->assign('zoekform', new forum\ForumZoekenForm());
		$this->smarty->assign('categorien', $this->model);
		$this->smarty->display('forum/forum.tpl');
	}

}
