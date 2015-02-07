<?php

/**
 * InstantSearchForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class InstantSearchForm extends Formulier {

	public function __construct() {
		parent::__construct(null, '/ledenlijst?status=ALL');
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

			if (LidInstellingen::get('zoeken', 'commissies') === 'ja') {
				$this->suggestions['Commissie'] = '/groepen/commissies/zoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'woonoorden') === 'ja') {
				$this->suggestions['Woonoord/Huis'] = '/groepen/woonoorden/zoeken/?q=';
			}

			$this->suggestions['Leden'] = '/tools/naamsuggesties/leden/?status=&' . LidInstellingen::get('zoeken', 'leden') . 'q=';

			if (LidInstellingen::get('zoeken', 'agenda') === 'ja') {
				$this->suggestions['Agenda'] = '/agenda/zoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'forum') === 'ja') {
				$this->suggestions['Forum'] = '/forum/titelzoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'fotoalbum') === 'ja') {
				$this->suggestions['Fotoalbum'] = '/fotoalbum/zoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'wiki') === 'ja') {
				$this->suggestions['Wiki'] = '/tools/wikisuggesties/?q=';
			}

			if (LidInstellingen::get('zoeken', 'documenten') === 'ja') {
				$this->suggestions['Documenten'] = '/documenten/zoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'boeken') === 'ja') {
				$this->suggestions['Boeken'] = '/bibliotheek/zoeken/?q=';
			}
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
		$html = '';
		foreach (array('commissies', 'woonoorden', 'leden', 'agenda', 'forum', 'fotoalbum', 'wiki', 'documenten', 'boeken') as $option) {
			$html .= '<li><a href="#">';
			if (LidInstellingen::get('zoeken', $option) !== 'nee') {
				$html .= '<span class="fa fa-check"></span> ';
				if ($option === 'leden') {
					$html .= ucfirst(strtolower(LidInstellingen::get('zoeken', 'leden'))) . '</a></li>';
					break;
				}
			} else {
				$html .= '<span style="margin-right: 18px;"></span> ';
			}
			$html .= ucfirst($option) . '</a></li>';
		}
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
					<li><a onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Forum reacties</a></li>
					<li><a onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Wiki inhoud</a></li>
					<li class="divider"></li>
					<li class="dropdown-submenu">
						<a href="#">Snelzoeken</a>
						<ul class="dropdown-menu">
							<li><a href="/instellingen#lidinstellingenform-tab-Zoeken">Aanpassen...</a></li>
							<li class="divider"></li>
								<?= $html; ?>
						</ul>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

}
