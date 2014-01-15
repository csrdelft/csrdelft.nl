<?php

/*
 * class.forumcategoriecontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */

class ForumCategorieContent extends TemplateView {

	private $forumcategorie;

	function __construct($forumcategorie) {
		parent::__construct();
		$this->forumcategorie = $forumcategorie;
	}

	function getTitel() {
		$titel = 'Forum - ';
		$titel.=$this->forumcategorie->getNaam();
		return $titel;
	}

	function view() {

		if (!$this->forumcategorie->magBekijken()) {
			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>Dit gedeelte van het forum is niet zichtbaar voor u, of het bestaat &uuml;berhaupt niet.
				<a href="/communicatie/forum/">Terug naar het forum</a>';
			return;
		}


		$this->smarty->assign('categorie', $this->forumcategorie);

		$this->smarty->assign('melding', $this->getMelding());
		$this->smarty->display('forum/list_onderwerpen.tpl');
	}

}

?>
