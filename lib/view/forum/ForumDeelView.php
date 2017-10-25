<?php

namespace CsrDelft\view\forum;

use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumDradenReagerenModel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\MenuModel;

class ForumDeelView extends ForumView {

	private $paging;
	private $belangrijk;

	public function __construct(
		ForumDeel $deel,
		$paging = true,
		$belangrijk = null
	) {
		parent::__construct($deel, $deel->titel);
		$this->paging = ($paging AND ForumDradenModel::instance()->getAantalPaginas($deel->forum_id) > 1);
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
			foreach ($categorie->getForumDelen() as $newDeel) {
				$dropdown .= '<option value="/forum/deel/' . $newDeel->forum_id . '"';
				if ($newDeel->forum_id === $this->model->forum_id) {
					$dropdown .= ' selected="selected"';
				}
				$dropdown .= '>' . $newDeel->titel . '</option>';
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
		$dropdown .= '</select>';
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
