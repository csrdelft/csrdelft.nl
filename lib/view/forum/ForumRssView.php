<?php

namespace CsrDelft\view\forum;

use CsrDelft\model\security\LoginModel;

class ForumRssView extends ForumView {

	public function __construct(array $draden) {
		parent::__construct($draden);
	}

	public function view() {
		$this->smarty->assign('draden', $this->model);
		$this->smarty->assign('privatelink', LoginModel::getAccount()->getRssLink());
		$this->smarty->display('forum/rss.tpl');
	}

}
