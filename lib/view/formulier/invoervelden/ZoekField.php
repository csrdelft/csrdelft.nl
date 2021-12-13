<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\MenuItem;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\service\security\LoginService;

/**
 */
class ZoekField extends AutocompleteField
{

	public $type = 'search';

	public function __construct($name)
	{
		parent::__construct($name, null, null);
		$this->css_classes[] = 'form-control me-sm-2';
		$this->css_classes[] = 'clicktogo';
		$this->placeholder = 'Zoeken';
		$this->autoselect = true;
		$this->onkeydown = <<<JS
if (event.keyCode === 191 || event.keyCode === 220) { // forward and backward slash
	event.preventDefault();
}
JS;
		if (LoginService::mag(P_LEDEN_READ)) {

			$menuRepository = ContainerFacade::getContainer()->get(MenuItemRepository::class);

			if (lid_instelling('zoeken', 'favorieten') === 'ja') {
				$this->addSuggestions($menuRepository->getMenu(LoginService::getUid())->children);
			}
			if (lid_instelling('zoeken', 'menu') === 'ja') {
				$this->addSuggestions($menuRepository->flattenMenu($menuRepository->getMenu('main')));
			}

			$this->suggestions[] = '/zoeken?q=';

			if (lid_instelling('zoeken', 'wiki') === 'ja') {
				$this->suggestions['Wiki'] = '/wiki/lib/exe/ajax.php?call=csrlink_wikisuggesties&q=';
			}
		}
	}

	/**
	 * @param MenuItem[] $list
	 */
	private function addSuggestions($list)
	{
		foreach ($list as $item) {
			$parent = $item->parent;
			if ($parent && $parent->tekst != 'main') {
				if ($parent->tekst == LoginService::getUid()) { // werkomheen
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

	public function __toString()
	{
		$html = '';
		$lidInstellingenRepository = ContainerFacade::getContainer()->get(LidInstellingenRepository::class);
		foreach ($lidInstellingenRepository->getModuleKeys('zoeken') as $option) {
			$html .= '<a class="dropdown-item disabled" href="#">';
			$instelling = lid_instelling('zoeken', $option);
			if ($instelling !== 'nee') {
				$html .= '<span class="fa fa-check fa-fw me-2"></span> ';
				if ($option === 'leden') {
					$html .= ucfirst(strtolower($instelling)) . '</a>';
					continue;
				}
			} else {
				$html .= '<span class="fa fa-fw me-2"></span> ';
			}
			$html .= ucfirst($option) . '</a>';
		}
		$parent = parent::getHtml();
		return <<<HTML
<div class="form-inline d-flex flex-nowrap">
	{$parent}
	<div class="dropdown">
		<button id="cd-zoek-engines" class="btn btn-light dropdown-toggle ZoekFieldDropdown" data-bs-toggle="dropdown" aria-expanded="false">
			<span class="fa fa-search"></span>
			<span class="caret"></span>
		</button>
		<div class="dropdown-menu dropdown-menu-right" role="menu">
			<a href="#" class="dropdown-item" onclick="window.location.href = '/ledenlijst?status=OUDLEDEN&q=' + encodeURIComponent(document.querySelector('#{$this->getId()}').value);">
				Oudleden
			</a>
			<a href="#" class="dropdown-item" onclick="window.location.href = '/ledenlijst?status=ALL&q=' + encodeURIComponent(document.querySelector('#{$this->getId()}').value);">
				Iedereen
			</a>

			<a href="#" class="dropdown-item" onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent(document.querySelector('#{$this->getId()}').value);">
				Forum reacties
			</a>
			<a href="#" class="dropdown-item" onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent(document.querySelector('#{$this->getId()}').value);">
				Wiki inhoud
			</a>
			<a class="divider"></a>
			<div class="dropdown-submenu dropleft">
				<a class="dropdown-item dropdown-toggle" href="#" id="menu-snelzoeken">Snelzoeken</a>
				<div class="dropdown-menu" aria-labelledby="menu-snelzoeken">
					<a class="dropdown-item" href="/instellingen#instelling-zoeken">Aanpassen...</a>
					<a class="divider"></a>
					{$html}
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
	}

}
