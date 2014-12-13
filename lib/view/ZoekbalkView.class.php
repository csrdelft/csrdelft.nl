<?php

/**
 * ZoekbalkView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ZoekbalkView extends Formulier {

	public function __construct() {
		parent::__construct(null, 'cd-zoek-form', null);
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
		$this->placeholder = 'Zoek op titel';
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
			// Favorieten suggesties
			$favs = MenuModel::instance()->getMenu(LoginModel::getUid());
			foreach ($favs->getChildren() as $item) {
				if ($item->magBekijken()) {
					$this->suggestions[''][] = array(
						'url'	 => $item->link,
						'value'	 => $item->tekst . '<span class="lichtgrijs"> - Favorieten</span>'
					);
				}
			}
			// Menu suggesties
			$list = MenuModel::instance()->getList(MenuModel::instance()->getMenu('main'));
			foreach ($list as $item) {
				if ($item->magBekijken()) {
					$label = $item->tekst;
					$parent = $item->getParent();
					if ($parent AND $parent->tekst != 'main') {
						$label .= '<span class="lichtgrijs"> - ' . $parent->tekst . '</span>';
					}
					$this->suggestions[''][] = array(
						'url'	 => $item->link,
						'value'	 => $label
					);
				}
			}
			// Verticalen suggesties
			foreach (VerticalenModel::instance()->prefetch() as $verticale) {
				$this->suggestions[''][] = array(
					'url'	 => '/verticalen#' . $verticale->letter,
					'value'	 => $verticale->naam . '<span class="lichtgrijs"> - Verticalen</span>'
				);
			}
			// Forum categorien suggesties
			require_once 'model/ForumModel.class.php';
			foreach (ForumModel::instance()->getForumIndeling() as $categorie) {
				$this->suggestions[''][] = array(
					'url'	 => '/forum#' . $categorie->categorie_id,
					'value'	 => $categorie->titel . '<span class="lichtgrijs"> - Forum</span>'
				);
				// Forum delen suggesties
				foreach ($categorie->getForumDelen() as $deel) {
					$this->suggestions[''][] = array(
						'url'	 => '/forum/deel/' . $deel->forum_id,
						'value'	 => $deel->titel . '<span class="lichtgrijs"> - ' . $categorie->titel . '</span>'
					);
				}
			}
			// Document categorien suggesties
			require_once 'model/documenten/DocCategorie.class.php';
			foreach (DocCategorie::getAll() as $cat) {
				if ($cat->magBekijken()) {
					$this->suggestions[''][] = array(
						'url'	 => '/documenten/categorie/' . $cat->getID(),
						'value'	 => $cat->getNaam() . '<span class="lichtgrijs"> - Documenten</span>'
					);
				}
			}
			// Nog meer suggesties
			$this->suggestions['Leden'] = '/tools/naamsuggesties/leden/?q=';
			$this->suggestions['Agenda'] = '/agenda/zoeken/';
			$this->suggestions['Forum'] = '/forum/titelzoeken/';
			$this->suggestions['Groepen'] = '/tools/groepsuggesties/?q=';
			$this->suggestions['Fotoalbum'] = '/fotoalbum/zoeken/';
			$this->suggestions['Wiki'] = '/tools/wikisuggesties/?q=';
			$this->suggestions['Documenten'] = '/documenten/zoeken/';
			$this->suggestions['Boeken'] = '/bibliotheek/zoeken/';
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
					<li><a onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Forum</a></li>
					<li><a class="submit">Leden</a></li>
					<li><a onclick="window.location.href = '/ledenlijst?status=OUDLEDEN&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Oudleden</a></li>
					<li><a onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Wiki</a></li>
				</ul>
			</div>
		</div>
		<?php
	}

}
