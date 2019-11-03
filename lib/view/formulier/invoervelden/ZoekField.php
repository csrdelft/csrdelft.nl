<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\model\MenuModel;
use CsrDelft\model\security\LoginModel;

/**
 */
class ZoekField extends TextField {

	public $type = 'search';

	public function __construct($name) {
		parent::__construct($name, null, null);
		$this->css_classes[] = 'form-control';
		$this->css_classes[] = 'clicktogo';
		$this->placeholder = 'Zoek op titel';
		$this->autoselect = true;
		$this->onkeydown = <<<JS
if (event.keyCode === 191 || event.keyCode === 220) { // forward and backward slash
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
		if (LoginModel::mag(P_LEDEN_READ)) {

			if (lid_instelling('zoeken', 'favorieten') === 'ja') {
				$this->addSuggestions(MenuModel::instance()->getMenu(LoginModel::getUid())->getChildren());
			}
			if (lid_instelling('zoeken', 'menu') === 'ja') {
				$this->addSuggestions(MenuModel::instance()->flattenMenu(MenuModel::instance()->getMenu('main')));
			}

			$this->suggestions[] = '/zoeken?q=';

			if (lid_instelling('zoeken', 'wiki') === 'ja') {
				$this->suggestions['Wiki'] = '/wiki/lib/exe/ajax.php?call=csrlink_wikisuggesties&q=';
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
		foreach (LidInstellingenModel::instance()->getModuleKeys('zoeken') as $option) {
			$html .= '<a class="dropdown-item disabled" href="#">';
			$instelling = lid_instelling('zoeken', $option);
			if ($instelling !== 'nee') {
				$html .= '<span class="fa fa-check fa-fw mr-2"></span> ';
				if ($option === 'leden') {
					$html .= ucfirst(strtolower($instelling)) . '</a>';
					continue;
				}
			} else {
				$html .= '<span class="fa fa-fw mr-2"></span> ';
			}
			$html .= ucfirst($option) . '</a>';
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
                    <a href="#" class="dropdown-item" onclick="window.location.href = '/ledenlijst?status=OUDLEDEN&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Oudleden
                    </a>
                    <a href="#" class="dropdown-item" onclick="window.location.href = '/ledenlijst?status=ALL&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Iedereen
                    </a>

                    <a href="#" class="dropdown-item" onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Forum reacties
                    </a>
                    <a href="#" class="dropdown-item" onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">
                        Wiki inhoud
                    </a>
                    <a class="divider"></a>
                    <div class="dropdown-submenu">
                        <a class="dropdown-item" href="#">Snelzoeken</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="/instellingen#instelling-zoeken">Aanpassen...</a>
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
