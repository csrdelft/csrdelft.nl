<?php

/**
 * HtmlPage.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een HTML pagina met stylesheets en scripts.
 * 
 */
abstract class HtmlPage implements View {

	/**
	 * <BODY>
	 * @var View
	 */
	protected $body;
	/**
	 * <TITLE>
	 * @var string
	 */
	protected $titel;
	/**
	 * <CSS>
	 * @var array 
	 */
	private $stylesheets = array();
	/**
	 * <SCRIPT>
	 * @var array
	 */
	private $scripts = array();

	public function __construct(View $body, $titel) {
		$this->body = $body;
		$this->titel = $titel;
	}

	function getModel() {
		return $this->body;
	}

	function getTitel() {
		return $this->titel;
	}

	function getBody() {
		return $this->body;
	}

	/**
	 * Zorg dat de HTML pagina een stylesheet inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus.
	 */
	public function addStylesheet($sheet, $remote = false) {
		if (!$remote) {
			$sheet .= (DEBUG ? '.css' : '.min.css');
			$sheet .= '?' . filemtime(HTDOCS_PATH . $sheet);
		}
		$this->stylesheets[md5($sheet)] = $sheet;
	}

	public function getStylesheets() {
		return $this->stylesheets;
	}

	/**
	 * Zorg dat de HTML pagina een script inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus.
	 */
	public function addScript($script, $remote = false) {
		if (!$remote) {
			$script .= (DEBUG ? '.js' : '.min.js');
			$script .= '?' . filemtime(HTDOCS_PATH . $script);
		}
		$this->scripts[md5($script)] = $script;
	}

	public function getScripts() {
		return $this->scripts;
	}

	/**
	 * Genereert url voor samengevoegde en gecompressde css
	 *
	 * @param string $layout
	 * @param string $module
	 * @param string $extension (optioneel) recognized: 'css' en 'js'
	 * @throws Exception
	 * @return string url
	 */
	public function getCompressedStyleUrl($layout, $module, $extension = 'css') {
		$allowedextensions = array('css', 'js');
		if(!in_array($extension, $allowedextensions, true)){
			throw new Exception('Unknown extension: ' . hsc($extension));
		}
		list($timestamp, $cache_ok, /* $modules */, /* $files */) = $this->checkCache($layout, $module, $extension);
		$timestamp = ($cache_ok ? $timestamp : time());

		return '/tools/' . $extension . '.php?l=' . $layout . '&m=' . $module . '&' . $timestamp;
	}

	/**
	 * Genereert url voor samengevoegde en gecompressde js
	 *
	 * @param string $layout
	 * @param string $module
	 * @return string url
	 */
	public function getCompressedScriptUrl($layout, $module) {
		return $this->getCompressedStyleUrl($layout, $module, $extension = 'js');
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

		//als deze functie niet in wiki context wordt gedraaid, zijn enkele settings en includes nodig
		if (!defined('DOKU_INC')) {
			define('DOKU_INC', HTDOCS_PATH . 'wiki/');

			global $conf;
			// enkele instellingen, zie voor uitleg op https://www.dokuwiki.org/config
			// Let op: duplicaat van tools/js.php en tools/css.php
			$conf['dmode'] = 493;
			$conf['safemodehack'] = 0;
			$conf['allowdebug'] = 0;
			$conf['cachedir'] = DATA_PATH . 'compressorcache';
			//$conf['compress'] = DEBUG ? 0 : 1; //stripping of whitespace and comments
			$conf['cachetime'] = 100*60*60*24; // -1, 0, ..
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
		}

		// decide from where to get the layout
		$allowedlayouts = array('layout', 'layout2', 'layout3');
		if (!in_array($layout, $allowedlayouts)) {
			$layout = $allowedlayouts[0];
		}

		// elke module bestaat uit een set css-bestanden
		$modules = array();

		$selectedmodule = trim(preg_replace('/[^\w-]+/', '', $selectedmodule));
		if ($selectedmodule == 'general') {
			// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht
			$modules[] = 'general';

			if($extension == 'css') {
				//voeg modules toe afhankelijk van instelling
				$modules[] = LidInstellingen::get('layout', 'opmaak');
				if (LidInstellingen::get('layout', 'toegankelijk') == 'bredere letters') {
					$modules[] = 'bredeletters';
				}
				if (LidInstellingen::get('layout', 'sneeuw') != 'nee') {
					if (LidInstellingen::get('layout', 'sneeuw') == 'ja') {
						$modules[] = 'snowanim';
					} else {
						$modules[] = 'snow';
					}
				}
			}

			if (LidInstellingen::get('layout', 'minion') == 'ja') {
				$modules[] = 'minion';
			}

		} else {
			// een niet-algemene module gevraagd
			if ($selectedmodule) {
				$modules[] = $selectedmodule;
			}
		}

		$key = ($extension == 'js' ? 'scripts' : 'styles') . $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'] . $layout . implode('', $modules);
		$cache = new cache($key, '.' . $extension);

		// load style.ini/script.ini
		if($extension == 'js') {
			$inicontent = HtmlPage::js_csr_scriptini($layout);
			$section = 'scripts';
		} else {
			$inicontent = HtmlPage::css_csr_styleini($layout);
			$section = 'stylesheets';
		}


		// cache influencers
		$cache_files = array();
		$cache_files[] = HTDOCS_PATH . $layout . '/'.($extension == 'js' ? 'script' : 'style') . '.ini';
		$cache_files[] = HTDOCS_PATH . 'tools/' . $extension . '.php';
		$cache_files[] = LIB_PATH . 'defines.include.php';

		// Array of needed files and their web locations, the latter ones
		// are needed to fix relative paths in the stylesheets
		$files = array();
		foreach ($modules as $module) {
			$files[$module] = array();

			// read files
			if (isset($inicontent[$section][$module])) {
				$files[$module] = array_merge($files[$module], $inicontent[$section][$module]);
			}

			$cache_files = array_merge($cache_files, array_keys($files[$module]));
		}

		// check cache age & handle conditional request
		// This may exit if a cache can be used
		$cache_ok = $cache->useCache(array('files' => $cache_files));
		$timestamp = @filemtime($cache->cache);

		return array(
			$timestamp,
			$cache_ok,
			$modules,
			$files,
			$cache,
			$inicontent
		);
	}

	/**
	 * Load script ini contents
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @author Gerrit Uitslag <klapinklapin@gmail.com>
	 *
	 * @param string $layout the used layout
	 * @return array with keys 'scripts'
	 */
	static function js_csr_scriptini($layout) {
		$scripts = array(); // mode, file => base

		// load layout's script.ini
		$incbase = HTDOCS_PATH;
		$webbase = CSR_ROOT;
		$ini = $incbase . $layout . '/script.ini';
		if (file_exists($ini)) {
			$data = parse_ini_file($ini, true);

			// stylesheets
			if (is_array($data['scripts'])) foreach ($data['scripts'] as $module => $files) {
				foreach($files as $file) {
					if(DEBUG && substr($file, -7) == '.min.js') {
						$uncompressedfile = substr_replace($file, '', -7, 4);
						if(file_exists($incbase . $uncompressedfile)) {
							$file = $uncompressedfile;
						}
					}
					$scripts[$module][$incbase . $file] = $webbase;
				}
			}
		}

		return array(
			'scripts' => $scripts,
		);
	}

	/**
	 * Load style ini contents
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @author Gerrit Uitslag <klapinklapin@gmail.com>
	 *
	 * @param string $layout the used layout
	 * @return array with keys 'stylesheets' and 'replacements'
	 */
	static function  css_csr_styleini($layout) {
		$stylesheets = array(); // mode, file => base
		$replacements = array(); // placeholder => value

		// load template's style.ini
		$incbase = HTDOCS_PATH;
		$webbase = CSR_ROOT;
		$ini = $incbase . $layout . '/style.ini';
		if (file_exists($ini)) {
			$data = parse_ini_file($ini, true);

			// stylesheets
			if (is_array($data['stylesheets'])) foreach ($data['stylesheets'] as $module => $files) {
				foreach ($files as $file) {
					if(DEBUG && substr($file, -8) == '.min.css') {
						$uncompressedfile = substr_replace($file, '', -8, 4);
						if(file_exists($incbase . $uncompressedfile)) {
							$file = $uncompressedfile;
						}
					}
					$stylesheets[$module][$incbase . $file] = $webbase;
				}
			}

			// replacements
			if (isset($data['replacements']) && is_array($data['replacements'])) {
				$replacements = array_merge($replacements, css_fixreplacementurls($data['replacements'], $webbase));
			}
		}

		return array(
			'stylesheets' => $stylesheets,
			'replacements' => $replacements
		);
	}
}
