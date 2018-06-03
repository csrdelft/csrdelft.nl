<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\MenuModel;
use CsrDelft\model\security\LoginModel;

class ZoekField extends TextField {

	public $type = 'search';

	public function __construct($name) {
		parent::__construct($name, null, null);
		$this->css_classes[] = 'form-control';
		$this->css_classes[] = 'clicktogo';
		$this->placeholder = 'Zoek op titel';
		$this->onkeydown = <<<JS

if (event.keyCode === 13) { // enter
    $('#{$this->getId()}').parent().find('.tt-suggestion').first().trigger('click');
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
	window.formulier.formSubmit(event);
}
JS;
		if (LoginModel::mag('P_LEDEN_READ')) {

			if (LidInstellingenModel::get('zoeken', 'favorieten') === 'ja') {
				$this->addSuggestions(MenuModel::instance()->getMenu(LoginModel::getUid())->getChildren());
			}
			if (LidInstellingenModel::get('zoeken', 'menu') === 'ja') {
				$this->addSuggestions(MenuModel::instance()->flattenMenu(MenuModel::instance()->getMenu('main')));
			}

			$instelling = LidInstellingenModel::get('zoeken', 'leden');
			if ($instelling !== 'nee') {
				$this->suggestions['Leden'] = '/tools/naamsuggesties/leden/?status=' . $instelling . '&q=';
			}

			// TODO: bundelen om simultane verbindingen te sparen
			foreach (array('commissies', 'kringen', 'onderverenigingen', 'werkgroepen', 'woonoorden', 'groepen') as $option) {
				if (LidInstellingenModel::get('zoeken', $option) === 'ja') {
					$this->suggestions[ucfirst($option)] = '/groepen/' . $option . '/zoeken/?q=';
				}
			}

			if (LidInstellingenModel::get('zoeken', 'agenda') === 'ja') {
				$this->suggestions['Agenda'] = '/agenda/zoeken/?q=';
			}

			if (LidInstellingenModel::get('zoeken', 'forum') === 'ja') {
				$this->suggestions['Forum'] = '/forum/titelzoeken/?q=';
			}

			if (LidInstellingenModel::get('zoeken', 'fotoalbum') === 'ja') {
				$this->suggestions['Fotoalbum'] = '/fotoalbum/zoeken/?q=';
			}

			if (LidInstellingenModel::get('zoeken', 'wiki') === 'ja') {
				$this->suggestions['Wiki'] = '/tools/wikisuggesties/?q=';
			}

			if (LidInstellingenModel::get('zoeken', 'documenten') === 'ja') {
				$this->suggestions['Documenten'] = '/documenten/zoeken/?q=';
			}

			if (LidInstellingenModel::get('zoeken', 'boeken') === 'ja') {
				$this->suggestions['Boeken'] = '/bibliotheek/zoeken/?q=';
			}

			// Favorieten en menu tellen niet
			$max = 6;
			if (isset($this->suggestions[''])) {
				$max++;
			}
			if (count($this->suggestions) > $max) {
				setMelding('Meer dan 6 zoekbronnen tegelijk wordt niet ondersteund', 0);
			}
		}
	}

	private function addSuggestions(array $list) {
		foreach ($list as $item) {
			if ($item->magBekijken()) {
				$parent = $item->getParent();
				if ($parent AND $parent->tekst != 'main') {
					if ($parent->tekst == LoginModel::getUid()) { // werkomheen
						$parent->tekst = 'Favorieten';
					}
					$label = $parent->tekst;
				} else {
					$label = 'Menu';
				}
				$this->suggestions[''][] = array(
					'url' => $item->link,
					'label' => $label,
					'value' => $item->tekst
				);
			}
		}
	}

	public function view() {
		$html = '';
		foreach (array('favorieten', 'menu', 'leden', 'commissies', 'kringen', 'onderverenigingen', 'werkgroepen', 'woonoorden', 'groepen', 'agenda', 'forum', 'fotoalbum', 'wiki', 'documenten', 'boeken') as $option) {
			$html .= '<a class="dropdown-item" href="#">';
			$instelling = LidInstellingenModel::get('zoeken', $option);
			if ($instelling !== 'nee') {
				$html .= '<span class="fa fa-check"></span> ';
				if ($option === 'leden') {
					$html .= ucfirst(strtolower($instelling)) . '</a>';
					continue;
				}
			} else {
				$html .= '<span style="margin-right: 18px;"></span> ';
			}
			$html .= ucfirst($option) . '</a></li>';
		}
		?>
		<div class="input-group">
            <?= parent::getHtml() ?>
            <div class="input-group-append dropdown">
                <button id="cd-zoek-engines" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-expanded="false">
                    <span class="fa fa-search"></span>
                    <span class="caret"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-right" role="menu">
                    <a class="dropdown-item" onclick="window.location.href = '/ledenlijst?status=OUDLEDEN&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Oudleden
                    </a>
                    <a class="dropdown-item" onclick="window.location.href = '/ledenlijst?status=ALL&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Iedereen
                    </a>

                    <a class="dropdown-item" onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Forum reacties
                    </a>
                    <a class="dropdown-item" onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Wiki inhoud
                    </a>
                    <a class="divider"></a>
                    <div class="dropdown-submenu">
                        <a class="dropdown-item" href="#">Snelzoeken</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="/instellingen#lidinstellingenform-tab-Zoeken">Aanpassen...</a>
                            <a class="divider"></a>
							<?= $html; ?>
                        </div>
                    </div>
                </div>
            </div>
		</div>
		<?php
	}

}
