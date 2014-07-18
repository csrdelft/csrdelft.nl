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
		$this->titel = 'Forum';
		$this->smarty->assign('categorien', $this->model);
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

class ForumDeelView extends TemplateView {

	public function __construct(ForumDeel $deel, $belangrijk = null) {
		parent::__construct($deel);
		$this->titel = 'Forum | ' . $deel->titel;
		$this->smarty->assign('belangrijk', ($belangrijk === true ? '/belangrijk' : ''));
		$this->smarty->assign('deel', $this->model);
		$this->smarty->assign('categorien', ForumModel::instance()->getForum());
	}

	public function view() {
		$this->smarty->assign('post_form_tekst', $_SESSION['forum_concept']);
		$this->smarty->display('MVC/forum/deel.tpl');
	}

}

class ForumDeelForm extends Formulier {

	public function __construct(ForumDeel $deel) {
		parent::__construct($deel, 'beheerdeelforum', '/forum/beheren/' . $deel->forum_id);
		$this->css_classes[] = 'ReloadPage';

		$fields[] = new RequiredTextField('titel', $deel->titel, 'Titel');
		$fields[] = new TextField('omschrijving', $deel->omschrijving, 'Omschrijving');
		$fields[] = new TextField('rechten_lezen', $deel->rechten_lezen, 'Leesrechten');
		$fields[] = new TextField('rechten_posten', $deel->rechten_posten, 'Postrechten');
		$fields[] = new TextField('rechten_modereren', $deel->rechten_modereren, 'Modrechten');
		$fields[] = new IntField('volgorde', $deel->volgorde, 'Volgorde');

		$this->addFields($fields);
	}

}

class ForumDraadView extends TemplateView {

	public function __construct(ForumDraad $draad, ForumDeel $deel) {
		parent::__construct($draad);
		$this->titel = 'Forum | ' . $draad->titel;
		$this->smarty->assign('draad', $this->model);
		$this->smarty->assign('deel', $deel);
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
		echo '<div id="zijbalk_forum"><h1><a href="/forum/recent';
		if ($this->belangrijk === true) {
			echo '/1/belangrijk';
		}
		echo '">Forum';
		if ($this->belangrijk === true) {
			echo ' belangrijk';
		}
		echo '</a></h1>';
		foreach ($this->model as $draad) {
			$this->smarty->assign('draad', $draad);
			$posts = $draad->getForumPosts();
			$this->smarty->assign('post', reset($posts));
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

	public function __construct(array $draden, array $delen, $query = null) {
		parent::__construct($draden);
		$this->smarty->assign('resultaten', $this->model);
		$this->smarty->assign('delen', $delen);
		if ($query !== null) {
			$this->smarty->assign('query', $query);
			$this->titel = 'Zoekresultaten voor: "' . $query . '"';
		} else {
			$this->titel = 'Wacht op goedkeuring';
		}
	}

	public function view() {
		$this->smarty->display('MVC/forum/resultaten.tpl');
	}

}
