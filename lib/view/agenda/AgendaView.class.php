<?php

namespace CsrDelft\view\agenda;

use CsrDelft\view\SmartyTemplateView;

/**
 * AgendaView.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Klasse voor het weergeven begin agenda-gerelateerde dingen.
 */
abstract class AgendaView extends SmartyTemplateView {

	public function getBreadcrumbs() {
		return '<a href="/agenda" title="Agenda"><span class="fa fa-calendar module-icon"></span></a>';
	}

}
