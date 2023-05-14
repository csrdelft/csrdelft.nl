<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\MenuItem;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\Icon;

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
			$menuRepository = ContainerFacade::getContainer()->get(
				MenuItemRepository::class
			);

			if (InstellingUtil::lid_instelling('zoeken', 'favorieten') === 'ja') {
				$favorietenMenu = $menuRepository->getMenu(LoginService::getUid());
				if ($favorietenMenu) {
					$this->addSuggestions($favorietenMenu->children);
				}
			}
			if (InstellingUtil::lid_instelling('zoeken', 'menu') === 'ja') {
				$this->addSuggestions(
					$menuRepository->flattenMenu($menuRepository->getMenu('main'))
				);
			}

			$this->suggestions[] = '/zoeken?q=';
		}
	}

	/**
	 * @param MenuItem[]|null $list
	 */
	private function addSuggestions($list)
	{
		if (!$list) {
			return;
		}
		$uid = LoginService::getUid();
		foreach ($list as $item) {
			$parent = $item->parent;
			if ($parent && $parent->tekst != 'main') {
				if ($parent->tekst == $uid) {
					// werkomheen
					$parent->tekst = 'Favorieten';
				}
				$label = $parent->tekst;
			} else {
				$label = 'Menu';
			}
			$this->suggestions[''][] = [
				'url' => $item->link,
				'label' => $label,
				'value' => $item->tekst,
			];
		}
	}

	public function __toString()
	{
		$html = '';
		$lidInstellingenRepository = ContainerFacade::getContainer()->get(
			LidInstellingenRepository::class
		);
		foreach ($lidInstellingenRepository->getModuleKeys('zoeken') as $option) {
			$html .= '<a class="dropdown-item disabled" href="#">';
			$instelling = $lidInstellingenRepository->getValue('zoeken', $option);
			if ($instelling !== 'nee') {
				$html .= Icon::getTag('check', null, '', 'fa-fw me-2') . ' ';
				if ($option === 'leden') {
					$html .= ucfirst(strtolower($instelling)) . '</a>';
					continue;
				}
			} else {
				$html .= Icon::getTag('', null, '', 'fa-fw me-2') . ' ';
			}
			$html .= ucfirst($option) . '</a>';
		}
		$wikiUrl = ContainerFacade::getContainer()->getParameter('wiki_url');
		$zoekIcoon = Icon::getTag('search', null, 'Zoeken');
		$parent = parent::getHtml();
		return <<<HTML
<div class="form-inline d-flex flex-nowrap">
	{$parent}
	<div class="dropdown">
		<button id="cd-zoek-engines" class="btn btn-light dropdown-toggle ZoekFieldDropdown" data-bs-toggle="dropdown" aria-expanded="false">
			{$zoekIcoon}
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
			<a href="#" class="dropdown-item" onclick="window.location.href = '{$wikiUrl}/w/index.php?title=Speciaal%3AZoeken&fulltext=1&search=' + encodeURIComponent(document.querySelector('#{$this->getId()}').value);">
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
