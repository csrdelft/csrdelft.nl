<?php
/**
 * ForumDraadView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\forum;

use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumDradenReagerenModel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\forum\ForumPostsModel;

class ForumDraadView extends ForumView {

	private $paging;
	private $statistiek;
	private $ongelezen;
	private $gelezen_moment;

	public function __construct(ForumDraad $draad, $paging = true, $statistiek = false) {
		parent::__construct($draad, $draad->titel);
		$this->paging = ($paging AND ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) > 1);
		$this->statistiek = $statistiek;
		// Cache original gelezen moment before setting gelezen voor ongelezen streep
		$gelezen = $draad->getWanneerGelezen();
		if ($gelezen) {
			$this->gelezen_moment = strtotime($gelezen->datum_tijd);
			$this->ongelezen = $draad->isOngelezen();
		} else {
			$this->gelezen_moment = false;
			$this->ongelezen = true;
		}
	}

	public function getBreadcrumbs() {
		$deel = $this->model->getForumDeel();
		return parent::getBreadcrumbs() . ' » <span class="active">' . $deel->getForumCategorie()->titel . '</span> » <a href="/forum/deel/' . $deel->forum_id . '/' . ForumDradenModel::instance()->getPaginaVoorDraad($this->model) . '#' . $this->model->draad_id . '">' . $deel->titel . '</a>';
	}

	public function view() {
		$this->smarty->assign('zoekform', new ForumZoekenForm());
		$this->smarty->assign('draad', $this->model);
		$this->smarty->assign('paging', $this->paging);
		$this->smarty->assign('post_form_tekst', ForumDradenReagerenModel::instance()->getConcept($this->model->getForumDeel(), $this->model->draad_id));
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
