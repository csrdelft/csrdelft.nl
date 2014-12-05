<?php

/**
 * MenuView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van een menu waarbij afhankelijk van
 * de rechten van de gebruiker menu items wel
 * of niet worden getoond.
 */
abstract class MenuView extends SmartyTemplateView {

	public function __construct(MenuItem $tree_root) {
		parent::__construct($tree_root);
	}

	public function view() {
		$this->smarty->assign('root', $this->model);
	}

}

class MainMenuView extends MenuView {

	private $form;

	public function __construct() {
		parent::__construct(MenuModel::instance()->getMenu('main'));

		$this->form = new Formulier(null, 'cd-zoek-form', '/communicatie/lijst.php');
		$this->form->post = false;

		$fields[] = new HtmlComment('<div class="input-group"><div class="input-group-btn">');

		$field = new TextField('q', null, null);
		$fields[] = $field;
		$field->css_classes[] = 'menuzoekveld form-control';
		$field->onkeydown = <<<JS

if (event.keyCode === 13) { // enter
	$(this).trigger('typeahead:selected');
}
JS;
		foreach (MenuModel::instance()->find('link != ""') as $item) {
			if ($item->magBekijken()) {
				if ($item->tekst == LoginModel::getUid()) {
					$field->suggestions['menu'][] = array('url' => $item->link, 'value' => 'Favorieten');
				} else {
					$field->suggestions['menu'][] = array('url' => $item->link, 'value' => $item->tekst);
				}
			}
		}

		if (LoginModel::mag('P_LEDEN_READ')) {

			require_once 'MVC/model/ForumModel.class.php';
			foreach (ForumDelenModel::instance()->getForumDelenVoorLid(false) as $deel) {
				$field->suggestions['forum'][] = array('url' => '/forum/deel/' . $deel->forum_id, 'value' => $deel->titel);
			}

			$field->suggestions['leden'] = '/tools/naamsuggesties/leden?q=';
			$field->suggestions['groepen'] = '/tools/suggesties/groep?q=';
			$field->suggestions['draadjes'] = '/forum/titelzoeken/';
			$field->suggestions['documenten'] = '/tools/suggesties/document?q=';
			$field->suggestions['boeken'] = '/tools/suggesties/boek?q=';
		}

		$field->typeahead_selected = <<<JS

if (suggestion) {
	window.location.href = suggestion.url;
}
else {
	form_submit(event);
}
JS;
		$fields[] = new HtmlComment(<<<HTML
<button id="cd-zoek-engines" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><img src="http://plaetjes.csrdelft.nl/knopjes/search-16.png"> <span class="caret"></span></button>
<ul class="dropdown-menu dropdown-menu-right" role="menu">
	<li><a onclick="window.location.href='/wiki/hoofdpagina?do=search&id='+encodeURIComponent($('#{$field->getId()}').val());">Wiki</a></li>
	<li><a onclick="window.location.href='/forum/zoeken/'+encodeURIComponent($('#{$field->getId()}').val());">Forum</a></li>
	<li><span class="glyphicon glyphicon-ok"></span><a class="submit">Leden</a></li>
	<li><span class="glyphicon glyphicon-ok"></span><a class="submit">Groepen</a></li>
	<li><a>Documenten</a></li>
	<li><a>Boeken</a></li>
</ul>
</div></div>
HTML
		);

		$this->form->addFields($fields);
	}

	public function view() {
		parent::view();
		$this->smarty->assign('menuzoekform', $this->form);
		$this->smarty->display('MVC/menu/main_menu.tpl');
	}

}

class PageMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('MVC/menu/page.tpl');
	}

}

class BlockMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('MVC/menu/block.tpl');
	}

}
