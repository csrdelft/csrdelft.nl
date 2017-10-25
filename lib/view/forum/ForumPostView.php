<?php

namespace CsrDelft\view\forum;

use CsrDelft\model\entity\forum\ForumPost;

class ForumPostView extends ForumView {

	public function __construct(ForumPost $post) {
		parent::__construct($post);
	}

	public function view() {
		$this->smarty->assign('post', $this->model);
		$this->smarty->display('forum/post_lijst.tpl');
	}

}
