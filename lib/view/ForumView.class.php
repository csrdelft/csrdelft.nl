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
		return '<a href="/forum" title="Forum"><img src="/plaetjes/knopjes/chat-16.png" class="module-icon"></a>';
	}

}

class ForumOverzichtView extends ForumView {

	public function __construct() {
		parent::__construct(ForumModel::instance()->getForumIndelingVoorLid(), 'Forum');
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/forum/recent">Recent</a>';
	}

	public function view() {
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('categorien', $this->model);
		$this->smarty->display('forum/forum.tpl');
	}

}

class ForumZoekenForm extends Formulier {

	public function __construct() {
		parent::__construct(null, 'forumZoekenForm', '/forum/zoeken');
		$this->css_classes[] = 'hoverIntent';

		$fields[] = new HtmlComment('<div class="forumZoekenGeavanceerd hoverIntentContent verborgen">');
		$fields[] = new SelectField('datumsoort', 'laatst_gewijzigd', null, array('laatst_gewijzigd' => 'Laatste reactie', 'datum_tijd' => 'Aanmaak-datum'));
		$fields[] = new SelectField('ouderjonger', 'jonger', null, array('jonger' => 'Niet', 'ouder' => 'Wel'));
		$fields[] = new HtmlComment(' ouder dan ');
		$fields[] = new IntField('jaaroud', 1, null, 0, 99);
		$fields[] = new HtmlComment(' jaar</div>'); /*
		  $fields['l'] = new LidField('auteur', null, 'Auteur');
		  $fields['l']->no_preview = true; */

		$fields['z'] = new TextField('zoekopdracht', null, null);
		$fields['z']->placeholder = 'Zoeken in forum';
		$fields['z']->enter_submit = true;

		$this->addFields($fields);
	}

}

class ForumRssView extends ForumView {

	public function __construct(array $draden) {
		parent::__construct($draden);
	}

	public function view() {
		$this->smarty->assign('draden', $this->model);
		$this->smarty->assign('privatelink', LoginModel::instance()->getLid()->getRssLink());
		$this->smarty->display('forum/rss.tpl');
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
		foreach (ForumModel::instance()->getForumIndelingVoorLid() as $cat) {
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
		$this->smarty->display('forum/deel.tpl');
	}

}

class ForumDeelForm extends ModalForm {

	public function __construct(ForumDeel $deel) {
		parent::__construct($deel, 'beheerdeelforum', '/forum/beheren/' . $deel->forum_id);
		$this->titel = 'Deelforum beheren';
		$this->css_classes[] = 'ReloadPage PreventUnchanged';

		$lijst = array();
		foreach (ForumModel::instance()->prefetch() as $cat) {
			$lijst[$cat->categorie_id] = $cat->titel;
		}

		$fields[] = new SelectField('categorie_id', $deel->categorie_id, 'Categorie', $lijst);
		$fields[] = new RequiredTextField('titel', $deel->titel, 'Titel');
		$fields[] = new TextareaField('omschrijving', $deel->omschrijving, 'Omschrijving');
		$fields[] = new RechtenField('rechten_lezen', $deel->rechten_lezen, 'Lees-rechten');
		$fields[] = new RechtenField('rechten_posten', $deel->rechten_posten, 'Post-rechten');
		$fields[] = new RechtenField('rechten_modereren', $deel->rechten_modereren, 'Mod-rechten');
		$fields[] = new IntField('volgorde', $deel->volgorde, 'Volgorde');

		$fields['btn'] = new FormDefaultKnoppen();

		$delete = new DeleteKnop('/forum/opheffen/' . $deel->forum_id);
		$fields['btn']->addKnop($delete, true);

		$recount = new FormulierKnop('/forum/hertellen/' . $deel->forum_id, 'post popup ReloadPage', 'Hertellen', 'Alle posts en draden hertellen', '/famfamfam/calculator.png');
		$fields['btn']->addKnop($recount, true);

		$this->addFields($fields);
	}

}

class ForumDraadView extends ForumView {

	private $deel;
	private $paging;
	private $statistiek;
	private $ongelezen;
	private $gelezen_moment;

	public function __construct(ForumDraad $draad, ForumDeel $deel, $paging = true, $statistiek = false) {
		parent::__construct($draad, $draad->titel);
		$this->deel = $deel;
		$this->paging = ($paging AND ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) > 1);
		$this->statistiek = $statistiek;
		// cache old value for ongelezen streep
		$this->ongelezen = $draad->onGelezen();
		if ($draad->getWanneerGelezen()) {
			$this->gelezen_moment = strtotime($draad->getWanneerGelezen()->datum_tijd);
		} else {
			$this->gelezen_moment = false;
		}
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($this->model);
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
		$this->smarty->assign('categorien', ForumModel::instance()->getForumIndelingVoorLid());
		$this->smarty->assign('gedeeld_met_opties', ForumDelenModel::instance()->getForumDelenOptiesOmTeDelen($this->deel));
		if ($this->statistiek) {
			$this->smarty->assign('statistiek', true);
		}
		$this->smarty->assign('draad_ongelezen', $this->ongelezen);
		$this->smarty->assign('gelezen_moment', $this->gelezen_moment);
		$this->smarty->display('forum/draad.tpl');
	}

}

class ForumDraadReagerenView extends ForumView {

	public function __construct($lijst) {
		parent::__construct($lijst);
	}

	public function view() {
		$this->smarty->assign('reageren', $this->model);
		$this->smarty->display('forum/draad_reageren.tpl');
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
		echo '<div class="zijbalk_forum"><div class="zijbalk-kopje"><a href="/forum/recent';
		if ($this->belangrijk === true) {
			echo '/1/belangrijk';
		}
		echo '">Forum';
		if ($this->belangrijk === true) {
			echo ' belangrijk';
		}
		echo '</a></div>';
		foreach ($this->model as $draad) {
			$this->smarty->assign('draad', $draad);
			$this->smarty->display('forum/draad_zijbalk.tpl');
		}
		echo '</div>';
	}

}

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
			$this->smarty->assign('draad', $post->getForumDraad());
			$this->smarty->display('forum/post_zijbalk.tpl');
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
		$this->smarty->display('forum/post_lijst.tpl');
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

	public function __construct(array $draden, $query = null) {
		parent::__construct($draden);
		if ($query !== null) {
			//FIXME: verder zoeken $this->smarty->assign('query', $query);
			$this->titel = 'Zoekresultaten voor: "' . $query . '"';
		} else {
			$this->titel = 'Wacht op goedkeuring';
		}
	}

	public function view() {
		$this->smarty->assign('resultaten', $this->model);
		$this->smarty->display('forum/resultaten.tpl');
	}

}
