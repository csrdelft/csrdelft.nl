<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\common\CsrException;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetsTwigExtension extends AbstractExtension
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security)
	{
		$this->security = $security;
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('user_modules', [$this, 'getUserModules']),
			new TwigFunction(
				'css_asset',
				[$this, 'css_asset'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'js_asset',
				[$this, 'js_asset'],
				['is_safe' => ['html']]
			),
			new TwigFunction('asset_url', [$this, 'asset_url']),
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
		if (!$this->security->isGranted('ROLE_LOGGED_IN')) {
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
				$assetString .= "<link rel=\"stylesheet\" href=\"{$asset[0]}\" integrity=\"{$asset[1]}\" type=\"text/css\" media=\"{$media}\"/>\n";
			} else {
				$assetString .= "<link rel=\"stylesheet\" href=\"{$asset[0]}\" integrity=\"{$asset[1]}\" type=\"text/css\"/>\n";
			}
		}

		return $assetString;
	}

	public function js_asset(string $module)
	{
		$assetString = '';

		foreach ($this->module_asset($module, 'js') as $asset) {
			$assetString .= "<script type=\"text/javascript\" src=\"{$asset[0]}\" integrity=\"{$asset[1]}\"></script>\n";
		}

		return $assetString;
	}

	private function module_asset(string $module, string $extension)
	{
		$manifest = $this->readManifest();

		$relevantAssets = [];

		$entrypoints = $manifest['entrypoints'];

		if (!isset($entrypoints[$module])) {
			throw new CsrException("Entrypoint met naam {$module} bestaat niet.");
		}

		if (!isset($entrypoints[$module]['assets'][$extension])) {
			throw new CsrException(
				"Entrypoint met naam {$module} heeft geen extensie {$extension}"
			);
		}

		$assets = $manifest['entrypoints'][$module]['assets'][$extension];

		foreach ($assets as $asset) {
			$relevantAssets[] = ['/dist/' . $asset['src'], $asset['integrity']];
		}

		return $relevantAssets;
	}

	public function asset_url($name)
	{
		$manifest = $this->readManifest();

		if (!isset($manifest[$name])) {
			throw new CsrException("Asset met naam {$name} bestaat niet.");
		}

		$asset = $manifest[$name];

		return '/dist/' . $asset['src'];
	}

	/**
	 * @return mixed
	 */
	private function readManifest()
	{
		if (!file_exists(HTDOCS_PATH . 'dist/assets-manifest.json')) {
			throw new CsrException(
				'htdocs/dist/assets-manifest.json besaat niet, voer "yarn dev" uit om deze te genereren.'
			);
		}

		$manifest = json_decode(
			file_get_contents(HTDOCS_PATH . 'dist/assets-manifest.json'),
			true
		);
		return $manifest;
	}
}
