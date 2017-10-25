<?php

namespace CsrDelft\view\documenten;

use CsrDelft\view\SmartyTemplateView;


/**
 * DocumentenView.class.php  |  Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Overzicht van alle categorieën met een bepaald aantal documenten per
 * categorie, zeg maar de standaarpagina voor de documentenketzer.
 */
abstract class DocumentenView extends SmartyTemplateView {

	public function getBreadcrumbs() {
		return '<a href="/documenten" title="Documenten"><span class="fa fa-file-text module-icon"></span></a>';
	}

}
