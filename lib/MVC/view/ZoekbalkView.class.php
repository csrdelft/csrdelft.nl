<?php

/**
 * ZoekbalkView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ZoekbalkView extends Formulier {

	public function __construct() {
		parent::__construct(null, 'cd-zoek-form', '/communicatie/lijst.php');
		$this->post = false;

		$fields[] = new ZoekInputGroup('q');

		$this->addFields($fields);
	}

	public function view() {
		echo '<nav id="cd-lateral-nav">';
		parent::view();
		echo '<div id="mainmenu">';
		$sitemap = new SitemapView();
		$sitemap->view();
		echo '</div></nav>';
	}

}

class ZoekInputGroup extends TextField {

	public function __construct($name) {
		parent::__construct($name, null, null);
		$this->css_classes[] = 'menuzoekveld form-control';
		$this->onkeydown = <<<JS

if (event.keyCode === 13) { // enter
	$(this).trigger('typeahead:selected');
}
else if (event.keyCode === 191 || event.keyCode === 220) { // forward and backward slash
	event.preventDefault();
}
JS;
		$this->typeahead_selected = <<<JS

if (suggestion) {
	window.location.href = suggestion.url;
}
else {
	form_submit(event);
}
JS;
		if (LoginModel::mag('P_LEDEN_READ')) {

			$favs = MenuModel::instance()->getMenu(LoginModel::getUid());
			foreach ($favs->getChildren() as $item) {
				$this->suggestions['Favorieten'][] = array(
					'url'	 => $item->link,
					'value'	 => $item->tekst
				);
			}

			$list = MenuModel::instance()->getList(MenuModel::instance()->getMenu('main'));
			foreach ($list as $item) {
				if ($item->magBekijken()) {
					$label = $item->tekst;
					$parent = $item->getParent();
					if ($parent AND $parent->tekst != 'main') {
						$label .= '<span class="lichtgrijs"> - ' . $parent->tekst . '</span>';
					}
					$this->suggestions['Menu'][] = array(
						'url'	 => $item->link,
						'value'	 => $label
					);
					if ($item->tekst === 'Documenten') {
						require_once 'documenten/categorie.class.php';
						foreach (DocumentenCategorie::getAll() as $cat) {
							if ($cat->magBekijken()) {
								$this->suggestions['Menu'][] = array(
									'url'	 => '/communicatie/documenten/categorie/' . $cat->getID(),
									'value'	 => $cat->getNaam() . '<span class="lichtgrijs"> - Documenten</span>'
								);
							}
						}
					}
				}
			}
			require_once 'MVC/model/ForumModel.class.php';
			foreach (ForumModel::instance()->getForumIndeling() as $categorie) {
				foreach ($categorie->getForumDelen() as $deel) {
					$this->suggestions['Forum'][] = array(
						'url'	 => '/forum/deel/' . $deel->forum_id,
						'value'	 => $deel->titel . '<span class="lichtgrijs"> - ' . $categorie->titel . '</span>'
					);
				}
			}
			$this->suggestions['Leden'] = '/tools/naamsuggesties/leden/?q=';
			$this->suggestions['Agenda'] = '/agenda/zoeken/';
			$this->suggestions['Draadjes'] = '/forum/titelzoeken/';
			$this->suggestions['Groepen'] = '/tools/groepsuggesties/?q=';
			$this->suggestions['Documenten'] = '/tools/documentsuggesties/?q=';
			$this->suggestions['Boeken'] = '/communicatie/bibliotheek/zoeken/';
		}
	}

	public function view() {
		?>
		<div class="input-group">
			<div class="input-group-btn">
				<?= parent::getHtml() ?>
				<button id="cd-zoek-engines" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
					<img src="http://plaetjes.csrdelft.nl/knopjes/search-16.png">
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right" role="menu">
					<li><a class="submit">Ledenlijst</a></li>
					<li><a onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Wiki</a></li>
					<li><a onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Forum</a></li>
				</ul>
			</div>
		</div>
		<?php
	}

}
