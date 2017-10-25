<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\view\SmartyTemplateView;


/**
 * BibliotheekView.php
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 *
 */
abstract class AbstractBibliotheekView extends SmartyTemplateView {

	public function getBreadcrumbs() {
		return '<a href="/bibliotheek" title="Bibliotheek"><span class="fa fa-book module-icon"></span></a>';
	}

}
