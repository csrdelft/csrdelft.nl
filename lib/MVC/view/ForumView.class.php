<?php

/**
 * ForumView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van het forum.
 */
abstract class ForumView extends SmartyTemplateView {

	public function getBreadcrumbs() {
		return '<a href="/forum" title="Forum"><img src="' . CSR_PICS . '/knopjes/chat-16.png" class="module-icon"></a>';
	}

}

class ForumOverzichtView extends ForumView {

	public function __construct() {
		parent::__construct(ForumModel::instance()->getForumIndeling(), 'Forum');
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/forum/recent">Recent</a>';
	}

	public function view() {
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('categorien', $this->model);
		$this->smarty->display('MVC/forum/forum.tpl');
	}

}

class ForumZoekenForm extends Formulier {

	public function __construct() {
		parent::__construct(null, 'forumZoekenForm', '/forum/zoeken');
		$this->css_classes[] = 'hoverIntent';

		$fields['z'] = new TextField('zoekopdracht');
		$fields['z']->placeholder = 'Zoeken in forum';
		$fields['z']->onkeyup = "if (event.keyCode == 13) { this.form.submit(); };";
		$fields[] = new HtmlComment('<div class="forumZoekenGeavanceerd hoverIntentContent verborgen">');
		$fields[] = new VinkField('alleentitel', false, null, 'Alleen op titel zoeken');
		$fields[] = new HtmlComment('<div class="inline">');
		$fields[] = new KeuzeRondjeField('datumsoort', 'reactie', null, array('reactie' => 'Laatste reactie', 'gemaakt' => 'Aanmaak-datum'));
		$fields[] = new SelectField('ouderjonger', 'jonger', null, array('jonger' => 'Niet', 'ouder' => 'Wel'));
		$fields[] = new HtmlComment(' ouder dan ');
		$fields[] = new IntField('jaaroud', 1, null, 1);
		$fields[] = new HtmlComment(' jaar</div>'); /*
		  $fields['l'] = new LidField('auteur', null, 'Auteur');
		  $fields['l']->no_preview = true; */
		$fields[] = new HtmlComment('</div>');

		$this->addFields($fields);
	}

}

class ForumRssView extends ForumView {

	private $delen;

	public function __construct(array $draden, array $delen) {
		parent::__construct($draden);
		$this->delen = $delen;
	}

	public function view() {
		$this->smarty->assign('draden', $this->model);
		$this->smarty->assign('delen', $this->delen);
		$this->smarty->assign('privatelink', LoginModel::instance()->getLid()->getRssLink());
		$this->smarty->display('MVC/forum/rss.tpl');
	}

}

class ForumDeelView extends ForumView {

	private $paging;
	private $belangrijk;

	public function __construct(ForumDeel $deel, $paging = true, $belangrijk = null) {
		parent::__construct($deel, $deel->titel);
		$this->paging = ($paging AND ForumDradenModel::instance()->getAantalPaginas($deel->forum_id) > 1);
		$this->belangrijk = ($belangrijk ? '/belangrijk' : '');
	}

	public function getBreadcrumbs() {
		$dropdown = parent::getBreadcrumbs() . ' » <select name="forum_id" onchange="document.location.href=this.value;"><option value="/forum/recent">Recent gewijzigd</option>';
		foreach (ForumModel::instance()->getForumIndeling() as $cat) {
			$dropdown .= '<optgroup label="' . $cat->titel . '">';
			foreach ($cat->getForumDelen() as $newDeel) {
				$dropdown .= '<option value="/forum/deel/' . $newDeel->forum_id . '"';
				if ($newDeel->forum_id === $this->model->forum_id) {
					$dropdown .= ' selected="selected"';
				}
				$dropdown .= '>' . $newDeel->titel . '</option>';
			}
			$dropdown .= '</optgroup>';
		}
		$dropdown .='</select>';
		return $dropdown;
	}

	public function view() {
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('deel', $this->model);
		$this->smarty->assign('paging', $this->paging);
		$this->smarty->assign('belangrijk', $this->belangrijk);
		$this->smarty->assign('post_form_titel', ForumDradenReagerenModel::instance()->getConceptTitel($this->model));
		$this->smarty->assign('post_form_tekst', ForumDradenReagerenModel::instance()->getConcept($this->model));
		$this->smarty->assign('reageren', ForumDradenReagerenModel::instance()->getReagerenVoorDeel($this->model));
		$this->smarty->display('MVC/forum/deel.tpl');
	}

}

class ForumDeelForm extends ModalForm {

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
		$fields[] = new RechtenField('rechten_lezen', $deel->rechten_lezen, 'Lees-rechten');
		$fields[] = new RechtenField('rechten_posten', $deel->rechten_posten, 'Post-rechten');
		$fields[] = new RechtenField('rechten_modereren', $deel->rechten_modereren, 'Mod-rechten');
		$fields[] = new IntField('volgorde', $deel->volgorde, 'Volgorde');

		$fields['btn'] = new FormKnoppen(null, true, true, true);

		$delete = new DeleteKnop('/forum/opheffen/' . $deel->forum_id);
		$fields['btn']->addKnop($delete);

		$recount = new FormulierKnop('/forum/hertellen/' . $deel->forum_id, 'post modal ReloadPage', 'Hertellen', 'Alle posts en draden hertellen', '/famfamfam/calculator.png', true);
		$fields['btn']->addKnop($recount);

		$this->addFields($fields);
	}

}

class ForumDraadView extends ForumView {

	private $deel;
	private $paging;
	private $statistiek;

	public function __construct(ForumDraad $draad, ForumDeel $deel, $paging = true, $statistiek = false) {
		parent::__construct($draad, $draad->titel);
		$this->deel = $deel;
		$this->paging = ($paging AND ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) > 1);
		$this->statistiek = $statistiek;
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/forum/deel/' . $this->deel->forum_id . '/' . ForumDradenModel::instance()->getPaginaVoorDraad($this->model) . '#' . $this->model->draad_id . '">' . $this->deel->titel . '</a>';
	}

	public function view() {
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('draad', $this->model);
		$this->smarty->assign('deel', $this->deel);
		$this->smarty->assign('paging', $this->paging);
		$this->smarty->assign('post_form_tekst', ForumDradenReagerenModel::instance()->getConcept($this->deel, $this->model->draad_id));
		$this->smarty->assign('reageren', ForumDradenReagerenModel::instance()->getReagerenVoorDraad($this->model));
		$this->smarty->assign('categorien', ForumModel::instance()->getForumIndeling());
		$this->smarty->assign('gedeeld_met_opties', ForumDelenModel::instance()->getForumDelenOptiesOmTeDelen($this->deel));
		if ($this->statistiek) {
			$this->smarty->assign('statistiek', true);
		}
		$this->smarty->display('MVC/forum/draad.tpl');
	}

}

class ForumDraadReagerenView extends ForumView {

	public function __construct($lijst) {
		parent::__construct($lijst);
	}

	public function view() {
		$this->smarty->assign('reageren', $this->model);
		$this->smarty->display('MVC/forum/draad_reageren.tpl');
	}

}

/**
 * Requires ForumDraad[]
 */
class ForumDraadZijbalkView extends ForumView {

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
class ForumPostZijbalkView extends ForumView {

	private $draden;

	public function __construct(array $posts, array $draden) {
		parent::__construct($posts);
		$this->draden = $draden;
	}

	public function view() {
		$this->smarty->assign('draden', $this->draden);
		echo '<div class="zijbalk_forum"><h1><a href="/communicatie/profiel/' . LoginModel::getUid() . '/#forum">Forum (zelf gepost)</a></h1>';
		foreach ($this->model as $post) {
			$this->smarty->assign('post', $post);
			$this->smarty->display('MVC/forum/post_zijbalk.tpl');
		}
		echo '</div>';
	}

}

class ForumPostView extends ForumView {

	private $draad;
	private $deel;

	public function __construct(ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		parent::__construct($post);
		$this->draad = $draad;
		$this->deel = $deel;
	}

	public function view() {
		$this->smarty->assign('post', $this->model);
		$this->smarty->assign('draad', $this->draad);
		$this->smarty->assign('deel', $this->deel);
		$this->smarty->display('MVC/forum/post_lijst.tpl');
	}

}

/**
 * Requires id of deleted forumpost.
 */
class ForumPostDeleteView extends ForumView {

	public function view() {
		echo '<tr id="forumpost-row-' . $this->model . '" class="remove"><td></td></tr>';
	}

}

class ForumResultatenView extends ForumView {

	private $delen;

	public function __construct(array $draden, array $delen, $query = null) {
		parent::__construct($draden);
		$this->delen = $delen;
		if ($query !== null) {
			//FIXME: verder zoeken $this->smarty->assign('query', $query);
			$this->titel = 'Zoekresultaten voor: "' . $query . '"';
		} else {
			$this->titel = 'Wacht op goedkeuring';
		}
	}

	public function view() {
		$this->smarty->assign('resultaten', $this->model);
		$this->smarty->assign('delen', $this->delen);
		$this->smarty->display('MVC/forum/resultaten.tpl');
	}

}
