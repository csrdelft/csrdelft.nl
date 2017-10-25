<?php

namespace CsrDelft\view\forum;

use CsrDelft\model\security\LoginModel;

/**
 * Requires ForumPost[] and ForumDraad[]
 */
class ForumPostZijbalkView extends ForumView {

	public function __construct(array $posts) {
		parent::__construct($posts);
	}

	public function view() {
		echo '<div class="zijbalk_forum"><div class="zijbalk-kopje"><a href="/profiel/' . LoginModel::getUid() . '/#forum">Forum (zelf gepost)</a></div>';
		foreach ($this->model as $post) {
			$this->smarty->assign('post', $post);
			$this->smarty->display('forum/post_zijbalk.tpl');
		}
		echo '</div>';
	}

}
