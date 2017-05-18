<?php
namespace CsrDelft\view;

use CsrDelft\model\LidInstellingenModel;
use CsrDelft\view\View;


/**
 * CompressedLayout.abstract.php
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Gebruikt .ini files voor stylesheets en scripts per module en layout.
 *
 * @see htdocs/tools/css.php
 * @see htdocs/tools/js.php
 */
abstract class CompressedLayout extends HtmlPage {

	private $layout;

	public function __construct($layout, View $body, $titel) {
		parent::__construct($body, $titel);
		$this->layout = $layout;
	}

	protected function getLayout() {
		return $this->layout;
	}

	/**
	 * Add compressed css en js to page for module.
	 *
	 * @param string $module
	 */
	public function addCompressedResources($module) {
		$sheet = '/styles/' . $this->layout . '/' . $module . '.css';
		parent::addStylesheet($sheet, true);

		$script = '/scripts/' . $this->layout . '/' . $module . '.js';
		parent::addScript($script, true);
	}

	/**
	 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
	 * [elke module bestaat uit een set css- of js-bestanden]
	 *
	 * @param $module
	 * @param $extension
	 * @return array
	 */
	public static function getUserModules($module, $extension) {
		$modules = array();

		if ($module == 'front-page') {
			return array('general');
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

			return $modules;
		} else {
			// een niet-algemene module gevraagd
			if ($module) {
				$modules[] = $module;
				return $modules;
			}
			return $modules;
		}
	}
}
