<?php

namespace CsrDelft\view;

use CsrDelft\model\LidInstellingenModel;
use Stash\Driver\FileSystem as FileSystemDriver;
use Stash\Pool as CachePool;

/**
 * CompressedLayout.abstract.php
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Gebruikt .ini files voor stylesheets en scripts per module en layout.
 *
 * @see htdocs/tools/css.php
 * @see htdocs/tools/js.php
 */
abstract class CompressedLayout extends HtmlPage {
	/**
	 * @var string
	 */
	private $layout;

	/**
	 * CompressedLayout constructor.
	 *
	 * @param string $layout
	 * @param View $body
	 * @param string $titel
	 */
	public function __construct($layout, View $body, $titel) {
		parent::__construct($body, $titel);
		$this->layout = $layout;
	}

	protected function getLayout() {
		return $this->layout;
	}

	/**
	 * Controleer de cache.
	 *
	 * @param $layout
	 * @param $module
	 * @param $extension
	 *
	 * @param array $modules Dependencies
	 *
	 * @return string Hash voor de timestamp van de laatste cache.
	 */
	public function cacheHash($layout, $module, $extension, $modules = []) {
		$driver = new FileSystemDriver(['path' => DATA_PATH . 'assets/']);
		$cachePool = new CachePool($driver);
		$item = $cachePool->getItem(sprintf('/%s/%s/%s/%s', $extension, $layout, $module, hash('crc32', implode('', $modules))));

		if ($item->isHit()) {
			return hash('crc32', $item->getCreation()->format('U'));
		} else {
			// Er bestaat geen cache, alleen nadat cache geleegd is.
			return hash('crc32', date('U'));
		}
	}

	/**
	 * Add compressed css en js to page for module.
	 *
	 * @param string $module
	 */
	public function addCompressedResources($module) {
		$cssModules = static::getUserModules($module, 'css');
		$sheet = sprintf('/styles/%s/%s/%s/%s.css',
			$this->cacheHash($this->getLayout(), $module, 'css', $cssModules),
			hash('crc32', implode('', $cssModules)),
			$this->getLayout(),
			$module
		);
		parent::addStylesheet($sheet, true);

		$jsModules = static::getUserModules($module, 'js');
		$script = sprintf('/scripts/%s/%s/%s/%s.js',
			$this->cacheHash($this->getLayout(), $module, 'js', $jsModules),
			hash('crc32', implode('', $jsModules)),
			$this->getLayout(),
			$module
		);
		parent::addScript($script, true);
	}

	/**
	 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
	 * [elke module bestaat uit een set css- of js-bestanden]
	 *
	 * @param $module
	 * @param $extension
	 *
	 * @return array
	 */
	public static function getUserModules($module, $extension) {
		$modules = array();

		if ($module == 'front-page') {
			$modules[] = 'general';
		} elseif ($module == 'general') {
			// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht
			$modules[] = 'general';
			$modules[] = 'formulier';
			$modules[] = 'datatable';
			$modules[] = 'grafiek';

			if ($extension == 'css') {
				//voeg modules toe afhankelijk van instelling
				$modules[] = LidInstellingenModel::get('layout', 'opmaak');

				if (LidInstellingenModel::get('layout', 'toegankelijk') == 'bredere letters') {
					$modules[] = 'bredeletters';
				}
				if (LidInstellingenModel::get('layout', 'fx') == 'sneeuw') {
					$modules[] = 'fxsnow';
				} elseif (LidInstellingenModel::get('layout', 'fx') == 'space') {
					$modules[] = 'fxspace';
				}
			} elseif ($extension == 'js') {
				if (LidInstellingenModel::get('layout', 'fx') == 'wolken') {
					$modules[] = 'fxclouds';
				}
			}

			if (LidInstellingenModel::get('layout', 'minion') == 'ja') {
				$modules[] = 'minion';
			}
			if (LidInstellingenModel::get('layout', 'fx') == 'onontdekt') {
				$modules[] = 'fxonontdekt';
			} elseif (LidInstellingenModel::get('layout', 'fx') == 'civisaldo') {
				$modules[] = 'fxcivisaldo';
			}
		} else {
			// een niet-algemene module gevraagd
			$modules[] = $module;
		}

		return $modules;
	}
}
