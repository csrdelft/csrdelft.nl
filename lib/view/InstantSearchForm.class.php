<?php

/**
 * InstantSearchForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class InstantSearchForm extends Formulier {

	public function __construct() {
		parent::__construct(null, null);
		$this->post = false;
		$fields[] = new ZoekInputGroup('q');
		$this->addFields($fields);
	}

}

class ZoekInputGroup extends TextField {

	public $type = 'search';

	public function __construct($name) {
		parent::__construct($name, null, null);
		$this->css_classes[] = 'form-control';
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

			$this->addSuggestions(MenuModel::instance()->getMenu(LoginModel::getUid())->getChildren());
			$this->addSuggestions(MenuModel::instance()->flattenMenu(MenuModel::instance()->getMenu('main')));

			$this->suggestions['Leden'] = '/tools/naamsuggesties/leden/?q=';
			$this->suggestions['Agenda'] = '/agenda/zoeken/?q=';
			$this->suggestions['Forum'] = '/forum/titelzoeken/?q=';
			$this->suggestions['Fotoalbum'] = '/fotoalbum/zoeken/?q=';
			$this->suggestions['Wiki'] = '/tools/wikisuggesties/?q=';
			$this->suggestions['Documenten'] = '/documenten/zoeken/?q=';
			//$this->suggestions['Boeken'] = '/bibliotheek/zoeken/?q=';
		}
	}

	private function addSuggestions(array $list) {
		foreach ($list as $item) {
			if ($item->magBekijken()) {
				$label = $item->tekst;
				$parent = $item->getParent();
				if ($parent AND $parent->tekst != 'main') {
					if ($parent->tekst == LoginModel::getUid()) { // werkomheen
						$parent->tekst = 'Favorieten';
					}
					$label .= '<span class="lichtgrijs"> - ' . $parent->tekst . '</span>';
				}
				$this->suggestions[''][] = array(
					'url'	 => $item->link,
					'value'	 => $label
				);
			}
		}
	}

	public function view() {
		?>
		<div class="input-group">
			<div class="input-group-btn">
				<?= parent::getHtml() ?>
				<button id="cd-zoek-engines" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
					<span class="fa fa-search"></span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right" role="menu">
					<li><a onclick="window.location.href = '/ledenlijst?status=LEDEN&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Leden</a></li>
					<li><a onclick="window.location.href = '/ledenlijst?status=OUDLEDEN&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Oudleden</a></li>
					<!--li class="dropdown-submenu">
						<a href="#">Groepen</a>
						<ul class="dropdown-menu">
							<li><a href="#">TODO</a></li>
						</ul>
					</li-->
					<li><a onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Forum</a></li>
					<li><a onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Wiki</a></li>
				</ul>
			</div>
		</div>
		<?php
	}

}
