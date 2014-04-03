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

	public function getTitel() {
		return 'Forum';
	}

	public function view() {
		$this->smarty->display('MVC/forum/forum.tpl');
	}

}

class ForumRssView extends TemplateView {

	public function __construct(array $draden, array $delen) {
		parent::__construct($draden);
		$this->smarty->assign('draden', $this->model);
		$this->smarty->assign('delen', $delen);
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

	public function getTitel() {
		return 'Forum recent';
	}

	public function view() {
		$this->smarty->display('MVC/forum/recent.tpl');
	}

}

class ForumDeelView extends TemplateView {

	public function __construct(ForumDeel $deel) {
		parent::__construct($deel);
		$this->smarty->assign('deel', $this->model);
		$this->smarty->assign('categorien', ForumModel::instance()->getForum());
	}

	public function getTitel() {
		return 'Forum | ' . $this->model->titel;
	}

	public function view() {
		$this->smarty->assign('post_form_tekst', $_SESSION['forum_concept']);
		$this->smarty->display('MVC/forum/deel.tpl');
	}

}

class ForumDraadView extends TemplateView {

	public function __construct(ForumDraad $draad, ForumDeel $deel) {
		parent::__construct($draad);
		$this->smarty->assign('draad', $this->model);
		$this->smarty->assign('deel', $deel);
	}

	public function getTitel() {
		return 'Forum | ' . $this->model->titel;
	}

	public function view() {
		$this->smarty->assign('post_form_tekst', $_SESSION['forum_concept']);
		$this->smarty->display('MVC/forum/draad.tpl');
	}

}

/**
 * Requires ForumDraad[]
 */
class ForumDraadZijbalkView extends TemplateView {

	private $belangrijk;

	public function __construct(array $draden, $belangrijk) {
		parent::__construct($draden);
		$this->belangrijk = $belangrijk;
	}

	public function view() {
		echo '<div id="zijbalk_forum"><h1><a href="/forum/recent">Forum';
		if ($this->belangrijk) {
			echo ' belangrijk';
		}
		echo '</a></h1>';
		foreach ($this->model as $draad) {
			$this->smarty->assign('draad', $draad);
			$this->smarty->assign('posts', $draad->getForumPosts());
			$this->smarty->display('MVC/forum/draad_zijbalk.tpl');
		}
		echo '</div>';
	}

}

/**
 * Requires ForumPost[] and ForumDraad[]
 */
class ForumPostZijbalkView extends TemplateView {

	public function __construct(array $posts, array $draden) {
		parent::__construct($posts);
		$this->smarty->assign('draden', $draden);
	}

	public function view() {
		echo '<div id="zijbalk_forum"><h1><a href="/communicatie/profiel/' . LoginLid::instance()->getUid() . '/#forum">Forum (zelf gepost)</a></h1>';
		foreach ($this->model as $post) {
			$this->smarty->assign('post', $post);
			$this->smarty->display('MVC/forum/post_zijbalk.tpl');
		}
		echo '</div>';
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

class ForumResultatenView extends TemplateView {

	public function __construct(array $draden, ForumDeel $dummy, $query) {
		parent::__construct($query);
		$this->smarty->assign('deel', $dummy);
		$this->smarty->assign('resultaten', $draden);
	}

	public function getTitel() {
		return 'Zoekresultaten voor: "' . $this->model . '"';
	}

	public function view() {
		$this->smarty->display('MVC/forum/resultaten.tpl');
	}

}
