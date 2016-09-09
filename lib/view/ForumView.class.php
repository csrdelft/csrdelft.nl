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
		return '<a href="/forum" title="Forum"><span class="fa fa-wechat module-icon"></span></a>';
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
		parent::__construct(null, '/forum/zoeken');
		$this->formId = 'forumZoekenForm';
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
		$this->smarty->assign('privatelink', LoginModel::getAccount()->getRssLink());
		$this->smarty->display('forum/rss.tpl');
	}

}

class ForumDeelView extends ForumView {

	private $paging;
	private $belangrijk;

	public function __construct(ForumDeel $forum, $paging = true, $belangrijk = null) {
		parent::__construct($forum, $forum->titel);
		$this->paging = ($paging AND ForumDradenModel::instance()->getAantalPaginas($forum->id) > 1);
		$this->belangrijk = ($belangrijk ? '/belangrijk' : '');
	}

	public function getBreadcrumbs() {
		$dropdown = parent::getBreadcrumbs();
		if ($this->model->categorie_id) {
			$dropdown .= ' » ' . $this->model->getForumCategorie()->titel;
		}
		$js = "if (this.value.substr(0,4) === 'http') { window.open(this.value); } else { window.location.href = this.value; }";
		$dropdown .= ' » <select name="forum_id" onchange="' . $js . '">';
		$dropdown .= '<option value="/forum/recent/belangrijk"';
		if ($this->model->titel === 'Belangrijk recent gewijzigd') {
			$dropdown .= ' selected="selected"';
		}
		$dropdown .= '>Belangrijk recent gewijzigd</option>';
		$dropdown .= '<option value="/forum/recent"';
		if ($this->model->titel === 'Recent gewijzigd') {
			$dropdown .= ' selected="selected"';
		}
		$dropdown .= '>Recent gewijzigd</option>';
		foreach (ForumModel::instance()->getForumIndelingVoorLid() as $categorie) {
			$dropdown .= '<optgroup label="' . $categorie->titel . '">';
			foreach ($categorie->getForumDelen() as $newForum) {
				$dropdown .= '<option value="/forum/deel/' . $newForum->id . '"';
				if ($newForum->id === $this->model->id) {
					$dropdown .= ' selected="selected"';
				}
				$dropdown .= '>' . $newForum->titel . '</option>';
			}
			$dropdown .= '</optgroup>';
		}
		foreach (MenuModel::instance()->getMenu('remotefora')->getChildren() as $remotecat) {
			if ($remotecat->magBekijken()) {
				$dropdown .= '<optgroup label="' . $remotecat->tekst . '">';
				foreach ($remotecat->getChildren() as $remoteforum) {
					if ($remoteforum->magBekijken()) {
						$dropdown .= '<option value="' . $remoteforum->link . '">' . $remoteforum->tekst . '</option>';
					}
				}
				$dropdown .= '</optgroup>';
			}
		}
		$dropdown .='</select>';
		return $dropdown;
	}

	public function view() {
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('forum', $this->model);
		$this->smarty->assign('paging', $this->paging);
		$this->smarty->assign('belangrijk', $this->belangrijk);
		$this->smarty->assign('post_form_titel', ForumDradenReagerenModel::instance()->getConceptTitel($this->model));
		$this->smarty->assign('post_form_tekst', ForumDradenReagerenModel::instance()->getConcept($this->model));
		$this->smarty->assign('reageren', ForumDradenReagerenModel::instance()->getReagerenVoorDeel($this->model));
		$this->smarty->display('forum/deel.tpl');
	}

}

class ForumDeelForm extends ModalForm {

	public function __construct(ForumDeel $forum) {
		parent::__construct($forum, '/forum/beheren/' . $forum->id);
		$this->titel = 'Deelforum beheren';
		$this->css_classes[] = 'ReloadPage';
		$this->css_classes[] = 'PreventUnchanged';

		$lijst = array();
		foreach (ForumModel::instance()->prefetch() as $categorie) {
			$lijst[$categorie->id] = $categorie->titel;
		}

		$fields[] = new SelectField('categorie_id', $forum->categorie_id, 'Categorie', $lijst);
		$fields[] = new RequiredTextField('titel', $forum->titel, 'Titel');
		$fields[] = new TextareaField('omschrijving', $forum->omschrijving, 'Omschrijving');
		$fields[] = new RechtenField('rechten_lezen', $forum->rechten_lezen, 'Lees-rechten');
		$fields[] = new RechtenField('rechten_posten', $forum->rechten_posten, 'Post-rechten');
		$fields[] = new RechtenField('rechten_modereren', $forum->rechten_modereren, 'Mod-rechten');
		$fields[] = new IntField('volgorde', $forum->volgorde, 'Volgorde');

		$fields['btn'] = new FormDefaultKnoppen();

		$delete = new DeleteKnop('/forum/opheffen/' . $forum->id);
		$fields['btn']->addKnop($delete, true);

		$this->addFields($fields);
	}

}

class ForumDraadView extends ForumView {

	private $paging;
	private $statistiek;
	private $ongelezen;
	private $gelezen_moment;

	public function __construct(ForumDraad $draad, $paging = true, $statistiek = false) {
		parent::__construct($draad, $draad->titel);
		$this->paging = ($paging AND ForumPostsModel::instance()->getAantalPaginas($draad->id) > 1);
		$this->statistiek = $statistiek;
		// cache old value for ongelezen streep
		$this->ongelezen = $draad->isOngelezen();
		if ($draad->getWanneerGelezen()) {
			$this->gelezen_moment = strtotime($draad->getWanneerGelezen()->datum_tijd);
		} else {
			$this->gelezen_moment = false;
		}
	}

	public function getBreadcrumbs() {
		$forum = $this->model->getForumDeel();
		return parent::getBreadcrumbs() . ' » <span class="active">' . $forum->getForumCategorie()->titel . '</span> » <a href="/forum/deel/' . $forum->id . '/' . ForumDradenModel::instance()->getPaginaVoorDraad($this->model) . '#' . $this->model->id . '">' . $forum->titel . '</a>';
	}

	public function view() {
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('draad', $this->model);
		$this->smarty->assign('paging', $this->paging);
		$this->smarty->assign('post_form_tekst', ForumDradenReagerenModel::instance()->getConcept($this->model->getForumDeel(), $this->model->id));
		$this->smarty->assign('reageren', ForumDradenReagerenModel::instance()->getReagerenVoorDraad($this->model));
		$this->smarty->assign('categorien', ForumModel::instance()->getForumIndelingVoorLid());
		$this->smarty->assign('gedeeld_met_opties', ForumDelenModel::instance()->getForumDelenOptiesOmTeDelen($this->model->getForumDeel()));
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
		echo '</a>';
		$aantal = ForumPostsModel::instance()->getAantalWachtOpGoedkeuring();
		if ($aantal > 0 AND LoginModel::mag('P_FORUM_MOD')) {
			echo ' &nbsp;<a href="/forum/wacht" class="badge" title="' . $aantal . ' forumbericht' . ($aantal === 1 ? '' : 'en') . ' wacht' . ($aantal === 1 ? '' : 'en') . ' op goedkeuring">' . $aantal . '</a>';
		}
		echo '</div>';
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
			$this->smarty->display('forum/post_zijbalk.tpl');
		}
		echo '</div>';
	}

}

class ForumPostView extends ForumView {

	public function __construct(ForumPost $post) {
		parent::__construct($post);
	}

	public function view() {
		$this->smarty->assign('post', $this->model);
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
