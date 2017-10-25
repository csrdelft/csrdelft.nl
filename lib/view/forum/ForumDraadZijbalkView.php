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
