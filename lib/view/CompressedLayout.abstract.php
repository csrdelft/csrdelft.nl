<?php

require_once 'view/HtmlPage.abstract.php';

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
		list($timestamp, $cache_ok, /* $modules */, /* $files */) = self::checkCache($this->layout, $module, 'css');
		$sheet = '/tools/css.php?l=' . $this->layout . '&m=' . $module . '&' . ($cache_ok ? $timestamp : time());
		parent::addStylesheet($sheet, true);

		list($timestamp, $cache_ok, /* $modules */, /* $files */) = self::checkCache($this->layout, $module, 'js');
		$script = '/tools/js.php?l=' . $this->layout . '&m=' . $module . '&' . ($cache_ok ? $timestamp : time());
		parent::addScript($script, true);
	}

	/**
	 * Geeft timestamp en status van de cache terug, en lijstjes van modules en bestanden die gecombineerd worden.
	 *
	 * @param string $layout layout naam
	 * @param string $selectedmodule naam van een module (een set van js/css-bestanden)
	 * @param bool $extension true: js, false: css
	 * @return array met: string key en array met alle modules die geladen moeten worden
	 */
	public static function checkCache($layout, $selectedmodule, $extension) {
		global $conf;

		//als deze functie niet in wiki context wordt gedraaid, zijn enkele settings en includes nodig
		if (!defined('DOKU_INC')) {
			define('DOKU_INC', HTDOCS_PATH . 'wiki/');

			// enkele instellingen, zie voor uitleg op https://www.dokuwiki.org/config
			// Let op: duplicaat van tools/js.php en tools/css.php
			$conf['dmode'] = 493;
			$conf['safemodehack'] = 0;
			$conf['allowdebug'] = 0;
			$conf['cachedir'] = DATA_PATH . 'compressorcache';
			//$conf['compress'] = DEBUG ? 0 : 1; //stripping of whitespace and comments
			$conf['cachetime'] = 100 * 60 * 60 * 24; // -1, 0, ..
			$conf['cssdatauri'] = 0; //filesize in bytes. Embed images below the thresshold in css. (Bad supported by IE < 8)

			require_once HTDOCS_PATH . 'wiki/inc/cache.php';
			require_once HTDOCS_PATH . 'wiki/inc/common.php';
			require_once HTDOCS_PATH . 'wiki/inc/Input.class.php';
			require_once HTDOCS_PATH . 'wiki/inc/pageutils.php';
			require_once HTDOCS_PATH . 'wiki/inc/infoutils.php';
			require_once HTDOCS_PATH . 'wiki/inc/io.php';
			require_once HTDOCS_PATH . 'wiki/inc/utf8.php';

			// input handle class
			global $INPUT;
			$INPUT = new Input();
		} elseif (substr($conf['cachedir'], -15) != 'compressorcache') {
			//schakel naar csr cache, terwijl wij in wiki context runnen
			$conf['cachedirbackup'] = $conf['cachedir'];
			$conf['cachedir'] = DATA_PATH . 'compressorcache';
		}

		// decide from where to get the layout
		$allowedlayouts = array('layout', 'layout2', 'layout3', 'layout-owee');
		if (!in_array($layout, $allowedlayouts)) {
			$layout = $allowedlayouts[0];
		}

		// bepaal de benodigde modules, afhankelijk van instellingen
		$modules = self::getUserModules($selectedmodule, $extension);

		// initieer een cache
		$key = ($extension == 'js' ? 'scripts' : 'styles') . $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'] . $layout . implode('', $modules);
		$cache = new cache($key, '.' . $extension);

		// load style.ini/script.ini
		$inicontent = self::parseConfig($layout, $extension);

		// cache influencers
		$cache_files = array();
		$cache_files[] = ASSETS_PATH . $layout . '/' . ($extension == 'js' ? 'script' : 'style') . '.ini';
		$cache_files[] = HTDOCS_PATH . 'tools/' . $extension . '.php';
		$cache_files[] = LIB_PATH . 'defines.include.php';

		// Array of needed files and their web locations, the latter ones
		// are needed to fix relative paths in the stylesheets
		$files = array();
		foreach ($modules as $module) {
			$files[$module] = array();

			// read files
			if (isset($inicontent['files'][$module])) {
				$files[$module] = array_merge($files[$module], $inicontent['files'][$module]);
			}

			$cache_files = array_merge($cache_files, array_keys($files[$module]));
		}

		// check cache age
		// This is used for deciding if the cache can be used
		$cache_ok = $cache->useCache(array('files' => $cache_files));
		$timestamp = @filemtime($cache->cache);

		//pad herstellen als wij in wiki context runnen
		if (isset($conf['cachedirbackup'])) {
			$conf['cachedir'] = $conf['cachedirbackup'];
		}

		return array(
			$timestamp,
			$cache_ok,
			$modules,
			$files,
			$cache,
			$inicontent['replacements']
		);
	}

	/**
	 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
	 * [elke module bestaat uit een set css- of js-bestanden]
	 *
	 * @param $module
	 * @param $extension
	 * @return array
	 */
	private static function getUserModules($module, $extension) {
		$modules = array();

		if ($module == 'general') {
			// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht
			$modules[] = 'general';
			$modules[] = 'formulier';
			$modules[] = 'datatable';
			$modules[] = 'grafiek';

			if ($extension == 'css') {
				//voeg modules toe afhankelijk van instelling
				$modules[] = LidInstellingen::get('layout', 'opmaak');
				if (LidInstellingen::get('layout', 'toegankelijk') == 'bredere letters') {
					$modules[] = 'bredeletters';
				}
				if (LidInstellingen::get('layout', 'fx') == 'sneeuw') {
					$modules[] = 'fxsnow';
				} elseif (LidInstellingen::get('layout', 'fx') == 'space') {
					$modules[] = 'fxspace';
				} elseif (LidInstellingen::get('layout', 'fx') == 'onontdekt') {
                    $modules[] = 'fxonontdekt';
                }
			} elseif ($extension == 'js') {

				if (LidInstellingen::get('layout', 'fx') == 'wolken') {
					$modules[] = 'fxclouds';
				}
			}

			if (LidInstellingen::get('layout', 'minion') == 'ja') {
				$modules[] = 'minion';
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

	/**
	 * Load style ini contents
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @author Gerrit Uitslag <klapinklapin@gmail.com>
	 *
	 * @param string $layout the used layout
	 * @param string $extension 'js' or 'css'
	 * @return array with keys 'stylesheets' and 'replacements'
	 */
	private static function parseConfig($layout, $extension) {
		if ($extension == 'js') {
			$ininame = 'script';
			$sectionname = 'scripts';
		} else {
			$ininame = 'style';
			$sectionname = 'stylesheets';
		}

		$includes = array(); // mode, file => base
		$replacements = array(); // placeholder => value
		// load style.ini/script.ini
		$incbase = ASSETS_PATH;
		$webbase = ASSETS_DIR;
		$ini = $incbase . $layout . '/' . $ininame . '.ini';
		if (file_exists($ini)) {
			$data = parse_ini_file($ini, true);

			// stylesheets
			if (is_array($data[$sectionname]))
				foreach ($data[$sectionname] as $module => $files) {
					foreach ($files as $file) {

						//in DEBUG select the non-minified file, if available
						$minext = '.min.' . $extension;
						$length_mintex = strlen($minext);
						if (DEBUG && substr($file, -$length_mintex) == $minext) {
							$uncompressedfile = substr_replace($file, '', -$length_mintex, 4);
							if (file_exists($incbase . $uncompressedfile)) {
								$file = $uncompressedfile;
							}
						}

						$relativedir = dirname($file);
						$includes[$module][$incbase . $file] = $webbase . '/' . $relativedir . '/';
					}
				}

			// replacements
			if (isset($data['replacements']) && is_array($data['replacements'])) {
				$replacements = array_merge($replacements, css_fixreplacementurls($data['replacements'], $webbase));
			}
		}

		return array(
			'files'			 => $includes,
			'replacements'	 => $replacements
		);
	}

}
