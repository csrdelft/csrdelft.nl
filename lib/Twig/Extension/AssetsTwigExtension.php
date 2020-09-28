<?php


namespace CsrDelft\Twig\Extension;


use CsrDelft\common\CsrException;
use CsrDelft\service\security\LoginService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetsTwigExtension extends AbstractExtension
{
	public function getFunctions()
	{
		return [
			new TwigFunction('user_modules', [$this, 'getUserModules']),
			new TwigFunction('css_asset', [$this, 'css_asset'], ['is_safe' => ['html']]),
			new TwigFunction('js_asset', [$this, 'js_asset'], ['is_safe' => ['html']]),
		];
	}

	/**
	 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
	 * De modules zijn terug te vinden in /assets/sass
	 *
	 * @return array
	 */
	public function getUserModules()
	{
		if (!LoginService::mag(P_LOGGED_IN)) {
			return [];
		}

		$modules = [];

		//voeg modules toe afhankelijk van instelling
		$modules[] = 'thema-' . lid_instelling('layout', 'opmaak');

		// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht

		if (lid_instelling('layout', 'toegankelijk') == 'bredere letters') {
			$modules[] = 'bredeletters';
		}

		if (lid_instelling('layout', 'fx') == 'civisaldo') {
			$modules[] = 'effect-civisaldo';
		}

		return $modules;
	}

	/**
	 * Genereer een unieke url voor een asset.
	 *
	 * @param string $asset
	 * @return string
	 */
	public function css_asset(string $module, $media = null)
	{
		$assetString = '';

		foreach ($this->module_asset($module, 'css') as $asset) {
			if ($media) {
				$assetString .= "<link rel=\"stylesheet\" href=\"{$asset}\" type=\"text/css\" media=\"{$media}\"/>\n";
			} else {
				$assetString .= "<link rel=\"stylesheet\" href=\"{$asset}\" type=\"text/css\"/>\n";
			}
		}

		return $assetString;
	}

	public function js_asset(string $module)
	{
		$assetString = '';

		foreach ($this->module_asset($module, 'js') as $asset) {
			$assetString .= "<script type=\"text/javascript\" src=\"{$asset}\"></script>\n";
		}

		return $assetString;
	}

	private function module_asset(string $module, string $extension)
	{
		if (!file_exists(HTDOCS_PATH . 'dist/manifest.json')) {
			throw new CsrException('htdocs/dist/manifest.json besaat niet, voer "yarn dev" uit om deze te genereren.');
		}

		$manifest = json_decode(file_get_contents(HTDOCS_PATH . 'dist/manifest.json'), true);

		$relevantAssets = [];

		foreach ($manifest as $asset => $resource) {
			if (preg_match('/(^|~)(' . $module . ')([.~])/', $asset) && endsWith($asset, $extension)) {
				$relevantAssets[] = $resource;
			}
		}

		return $relevantAssets;
	}
}
