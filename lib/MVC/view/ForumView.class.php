<?php

/**
 * ForumView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van het forum.
 */
class ForumView extends TemplateView {

	public function __construct(array $categorien) {
		parent::__construct($categorien);
		$this->smarty->assign('categorien', $this->model);
	}

	public function view() {
		$this->smarty->display('MVC/forum/start.tpl');
	}

}

class ForumDeelView extends TemplateView {

	public function __construct(ForumDeel $deel) {
		parent::__construct($deel);
		$this->smarty->assign('deel', $this->model);
	}

	public function view() {
		$this->smarty->display('MVC/forum/deel.tpl');
	}

}

class ForumDraadView extends TemplateView {

	public function __construct(ForumDraad $draad, ForumDeel $deel) {
		parent::__construct($draad);
		$this->smarty->assign('draad', $this->model);
		$this->smarty->assign('deel', $deel);
	}

	public function view() {
		$this->smarty->display('MVC/forum/draad.tpl');
	}

}

class ForumPostView extends TemplateView {

	public function __construct(ForumPost $post) {
		parent::__construct($post);
		$this->smarty->assign('post', $this->model);
	}

	public function view() {
		$this->smarty->display('MVC/forum/post.tpl');
	}

}
