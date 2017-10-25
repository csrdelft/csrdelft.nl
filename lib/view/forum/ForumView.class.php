<?php

namespace CsrDelft\view\forum;

use CsrDelft\view\SmartyTemplateView;

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
