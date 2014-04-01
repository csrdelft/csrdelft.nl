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
		$this->smarty->display('MVC/forum/forum.tpl');
	}

}

class ForumRssView extends TemplateView {

	public function __construct(array $draden, array $titels) {
		parent::__construct($draden);
		$this->smarty->assign('draden', $this->model);
		$this->smarty->assign('titels', $titels);
		$this->smarty->assign('privatelink', LoginLid::instance()->getLid()->getRssLink());
	}

	public function view() {
		$this->smarty->display('MVC/forum/rss.tpl');
	}

}

class ForumRecentView extends TemplateView {

	public function __construct(array $draden) {
		parent::__construct($draden);
		$this->smarty->assign('draden', $this->model);
	}

	public function view() {
		$this->smarty->display('MVC/forum/recent.tpl');
	}

}

class ForumDeelView extends TemplateView {

	public function __construct(ForumDeel $deel, ForumCategorie $categorie = null) {
		parent::__construct($deel);
		$this->smarty->assign('deel', $this->model);
		$this->smarty->assign('categorie', $categorie);
	}

	public function view() {
		$this->smarty->assign('post_form_tekst', $_SESSION['forum_laatste_post_tekst']);
		$this->smarty->display('MVC/forum/deel.tpl');
	}

}

class ForumDraadView extends TemplateView {

	public function __construct(ForumDraad $draad, ForumDeel $deel, ForumCategorie $categorie) {
		parent::__construct($draad);
		$this->smarty->assign('draad', $this->model);
		$this->smarty->assign('deel', $deel);
		$this->smarty->assign('categorie', $categorie);
	}

	public function view() {
		$this->smarty->assign('post_form_tekst', $_SESSION['forum_laatste_post_tekst']);
		$this->smarty->display('MVC/forum/draad.tpl');
	}

}

class ForumPostView extends TemplateView {

	public function __construct(ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		parent::__construct($post);
		$this->smarty->assign('post', $this->model);
		$this->smarty->assign('draad', $draad);
		$this->smarty->assign('deel', $deel);
	}

	public function view() {
		$this->smarty->display('MVC/forum/post_lijst.tpl');
	}

}

/**
 * Requires id of deleted forumpost.
 */
class ForumPostDeleteView extends TemplateView {

	public function view() {
		echo '<tr id="forumpost-row-' . $this->model . '" class="remove"><td></td></tr>';
	}

}
