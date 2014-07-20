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
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('categorien', $this->model);
		$this->smarty->assign('verborgen_aantal', ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid());
	}

	public function view() {
		$this->smarty->display('MVC/forum/forum.tpl');
	}

}

class ForumZoekenForm extends Formulier {

	public function __construct() {
		parent::__construct(null, 'forumZoekenForm', '/forum/zoeken');
		$this->css_classes[] = 'hoverIntent';

		$fields['z'] = new TextField('zoekopdracht');
		$fields['z']->placeholder = 'Zoeken in forum';
		$fields[] = new HtmlComment('<div class="forumZoekenGeavanceerd hoverIntentContent" style="display: none;">');
		$fields[] = new VinkField('alleentitel', false, null, 'Alleen op titel zoeken');
		$fields[] = new HtmlComment('<div class="inline">');
		$fields['k'] = new KeuzeRondjeField('datumsoort', 'reactie', null, array('reactie' => 'Laatste reactie', 'gemaakt' => 'Aanmaak-datum'));
		$fields[] = new SelectField('ouderjonger', 'jonger', null, array('jonger' => 'Niet', 'ouder' => 'Wel'));
		$fields[] = new HtmlComment(' ouder dan ');
		$fields[] = new IntField('jaaroud', 1, null, 0);
		$fields[] = new HtmlComment(' jaar</div>');
		$fields['l'] = new LidField('auteur', null, 'Auteur');
		$fields['l']->no_preview = true;
		$fields[] = new HtmlComment('</div>');

		$this->addFields($fields);
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
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('belangrijk', ($belangrijk === true ? '/belangrijk' : ''));
		$this->smarty->assign('deel', $this->model);
		$this->smarty->assign('categorien', ForumModel::instance()->getForum());
		$this->smarty->assign('verborgen_aantal', ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid());
	}

	public function view() {
		$this->smarty->assign('post_form_tekst', $_SESSION['forum_concept']);
		$this->smarty->display('MVC/forum/deel.tpl');
	}

}

class ForumDeelForm extends PopupForm {

	public function __construct(ForumDeel $deel) {
		parent::__construct($deel, 'beheerdeelforum', '/forum/beheren/' . $deel->forum_id);
		$this->titel = 'Deelforum beheren';
		$this->css_classes[] = 'ReloadPage PreventUnchanged';

		$categorien = ForumModel::instance()->find(null, array(), 'volgorde ASC');
		$lijst = array();
		foreach ($categorien as $cat) {
			$lijst[$cat->categorie_id] = $cat->titel;
		}

		$fields[] = new SelectField('categorie_id', $deel->categorie_id, 'Categorie', $lijst);
		$fields[] = new RequiredTextField('titel', $deel->titel, 'Titel');
		$fields[] = new TextareaField('omschrijving', $deel->omschrijving, 'Omschrijving');
		$fields[] = new TextField('rechten_lezen', $deel->rechten_lezen, 'Lees-rechten');
		$fields[] = new TextField('rechten_posten', $deel->rechten_posten, 'Post-rechten');
		$fields[] = new TextField('rechten_modereren', $deel->rechten_modereren, 'Mod-rechten');
		$fields[] = new IntField('volgorde', $deel->volgorde, 'Volgorde');
		$fields['src'] = new SubmitResetCancel();
		$fields['src']->extraText = 'Verwijderen';
		$fields['src']->extraTitle = 'Deelforum opheffen';
		$fields['src']->extraIcon = 'cross';
		$fields['src']->extraUrl = '/forum/opheffen/' . $deel->forum_id;
		$fields['src']->extraActie = 'submit';
		$fields['src']->js = "$('#extraButton').unbind('click.action');$('#extraButton').bind('click.action', form_replace_action);";

		$this->addFields($fields);
	}

}

class ForumDraadView extends TemplateView {

	public function __construct(ForumDraad $draad, ForumDeel $deel) {
		parent::__construct($draad);
		$this->titel = 'Forum | ' . $draad->titel;
		$this->smarty->assign('zoekform', new ForumZoekenForm());
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
		echo '<div class="zijbalk_forum"><h1><a href="/forum/recent';
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
		echo '<div class="zijbalk_forum"><h1><a href="/communicatie/profiel/' . LoginLid::instance()->getUid() . '/#forum">Forum (zelf gepost)</a></h1>';
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
