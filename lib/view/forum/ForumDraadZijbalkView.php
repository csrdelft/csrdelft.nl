<?php
/**
 * ForumDraadZijbalkView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\forum;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\security\LoginModel;

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
		echo '<div class="zijbalk_forum">';
		foreach ($this->model as $draad) {
			$this->smarty->assign('draad', $draad);
			$this->smarty->display('forum/draad_zijbalk.tpl');
		}
		echo '</div>';
	}

}
