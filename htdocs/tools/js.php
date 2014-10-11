<?php
/**
 * C.S.R. Javascript creator based on DokuWiki JavaScript creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Gerrit Uitslag <klapinklapin@gmail.com>
 */

if (!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__) . '/../wiki/');

//reuse the Javascript functions of DokuWiki without triggering the main function js_out()
define('SIMPLE_TEST', 1);

require_once(DOKU_INC . 'lib/exe/js.php');


// overrule sommige instellingen, zie voor uitleg op https://www.dokuwiki.org/config
$conf['compress'] = 1; //stripping of whitespace and comments
$conf['allowdebug'] = 0;
$conf['cachedir'] = DATA_PATH . 'compressorcache';
$conf['cachetime'] = 100*60*60*24; // -1, 0, ..


//generate javascript file
header('Content-Type: text/javascript; charset=utf-8');
csr_js_out();


// ---------------------- C.S.R. functions ------------------------------

/**
 * Output all needed JavaScript
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function csr_js_out() {
	global $conf;
	global $INPUT;

	// decide from where to get the layout
	$layout = $INPUT->str('l');
	$allowedlayouts = array('layout', 'layout2', 'layout3');
	if (!in_array($layout, $allowedlayouts)) {
		$layout = $allowedlayouts[0];
	}

	// elke module bestaat uit een set scripts
	$modules = array();

	$activemodule = trim(preg_replace('/[^\w-]+/', '', $INPUT->str('m')));
	if($activemodule == 'general') {
		$modules[] = 'general';

		// modules toevoegen via instellingen
		if (LidInstellingen::get('layout', 'minion') == 'ja') {
			$modules[] = 'minion';
		}

	} else {
		// een niet-algemene module gevraagd
		if ($activemodule) {
			$modules[] = $activemodule;
		}
	}


	// The generated script depends on some dynamic options
	$cache = new cache('scripts' . $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'] . $layout . implode('', $modules), '.js');
	$cache->_event = 'JS_CACHE_USE';

//	// load minified version for some files
//	$min = $conf['compress'] ? '.min' : '';
//
//	// array of core files
//	$files = array(
//		DOKU_INC."lib/scripts/jquery/jquery$min.js",
//		DOKU_INC.'lib/scripts/jquery/jquery.cookie.js',
//		DOKU_INC."lib/scripts/jquery/jquery-ui$min.js",
//		DOKU_INC."lib/scripts/jquery/jquery-migrate$min.js",
//		DOKU_INC.'inc/lang/'.$conf['lang'].'/jquery.ui.datepicker.js',
//		DOKU_INC."lib/scripts/fileuploader.js",
//		DOKU_INC."lib/scripts/fileuploaderextended.js",
//		DOKU_INC.'lib/scripts/helpers.js',
//		DOKU_INC.'lib/scripts/delay.js',
//		DOKU_INC.'lib/scripts/cookie.js',
//		DOKU_INC.'lib/scripts/script.js',
//		DOKU_INC.'lib/scripts/tw-sack.js',
//		DOKU_INC.'lib/scripts/qsearch.js',
//		DOKU_INC.'lib/scripts/tree.js',
//		DOKU_INC.'lib/scripts/index.js',
//		DOKU_INC.'lib/scripts/drag.js',
//		DOKU_INC.'lib/scripts/textselection.js',
//		DOKU_INC.'lib/scripts/toolbar.js',
//		DOKU_INC.'lib/scripts/edit.js',
//		DOKU_INC.'lib/scripts/editor.js',
//		DOKU_INC.'lib/scripts/locktimer.js',
//		DOKU_INC.'lib/scripts/linkwiz.js',
//		DOKU_INC.'lib/scripts/media.js',
//# deprecated                DOKU_INC.'lib/scripts/compatibility.js',
//# disabled for FS#1958                DOKU_INC.'lib/scripts/hotkeys.js',
//		DOKU_INC.'lib/scripts/behaviour.js',
//		DOKU_INC.'lib/scripts/page.js',
//		tpl_incdir().'script.js',
//	);

	// load style.ini
	$styleini = js_csr_scriptini($layout);

	// cache influencers
	$cache_files = array();
	$cache_files[] = HTDOCS_PATH . $layout . '/script.ini';
	$cache_files[] = __FILE__;


	$files = array();
	foreach ($modules as $module) {
		$files[$module] = array();

		// collect scripts
		if (isset($styleini['scripts'][$module])) {
			$files[$module] = array_merge($files[$module], $styleini['scripts'][$module]);
		}

		$cache_files = array_merge($cache_files, array_keys($files[$module]));
	}


	// check cache age & handle conditional request
	// This may exit if a cache can be used
	$cache_ok = $cache->useCache(array('files' => $cache_files));
	http_cached($cache->cache, $cache_ok);

	// start output buffering and build the script
	ob_start();

//	$json = new JSON();
//	// add some global variables
//	print "var DOKU_BASE   = '".DOKU_BASE."';";
//	print "var DOKU_TPL    = '".tpl_basedir()."';";
//	print "var DOKU_COOKIE_PARAM = " . $json->encode(
//			array(
//				'path' => empty($conf['cookiedir']) ? DOKU_REL : $conf['cookiedir'],
//				'secure' => $conf['securecookie'] && is_ssl()
//			)).";";
//	// FIXME: Move those to JSINFO
//	print "var DOKU_UHN    = ".((int) useHeading('navigation')).";";
//	print "var DOKU_UHC    = ".((int) useHeading('content')).";";

//	// load JS specific translations
//	$lang['js']['plugins'] = js_pluginstrings();
//	$templatestrings = js_templatestrings();
//	if(!empty($templatestrings)) {
//		$lang['js']['template'] = $templatestrings;
//	}
//	echo 'LANG = '.$json->encode($lang['js']).";\n";

//	// load toolbar
//	toolbar_JSdefines('toolbar');

	// load files
	foreach ($modules as $module) {
		foreach ($files[$module] as $file => $location) {
			if (!file_exists($file)) continue;
			$ismin = (substr($file, -7) == '.min.js');
			$debugjs = ($conf['allowdebug'] && strpos($file, DOKU_INC . 'lib/scripts/') !== 0);

			echo "\n\n/* XXXXXXXXXX begin of " . str_replace(DOKU_INC, '', $file) . " XXXXXXXXXX */\n\n";
			if ($ismin) echo "\n/* BEGIN NOCOMPRESS */\n";
			if ($debugjs) echo "\ntry {\n";
			js_load($file);
			if ($debugjs) echo "\n} catch (e) {\n   logError(e, '" . str_replace(DOKU_INC, '', $file) . "');\n}\n";
			if ($ismin) echo "\n/* END NOCOMPRESS */\n";
			echo "\n\n/* XXXXXXXXXX end of " . str_replace(DOKU_INC, '', $file) . " XXXXXXXXXX */\n\n";
		}

	}

//	// init stuff
//	if($conf['locktime'] != 0){
//		js_runonstart("dw_locktimer.init(".($conf['locktime'] - 60).",".$conf['usedraft'].")");
//	}
	// init hotkeys - must have been done after init of toolbar
# disabled for FS#1958    js_runonstart('initializeHotkeys()');

	// end output buffering and get contents
	$js = ob_get_contents();
	ob_end_clean();

	// strip any source maps
	stripsourcemaps($js);

	// compress whitespace and comments
	if ($conf['compress']) {
		$js = js_compress($js);
	}

	$js .= "\n"; // https://bugzilla.mozilla.org/show_bug.cgi?id=316033

	http_cached_finish($cache->cache, $js);
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
function js_csr_scriptini($layout) {
	$scripts = array(); // mode, file => base

	// load layout's script.ini
	$incbase = HTDOCS_PATH;
	$webbase = CSR_ROOT;
	$ini = $incbase . $layout . '/script.ini';
	if (file_exists($ini)) {
		$data = parse_ini_file($ini, true);

		// stylesheets
		if (is_array($data['scripts'])) foreach ($data['scripts'] as $file => $module) {
			$scripts[$module][$incbase . $file] = $webbase;
		}
	}

	return array(
		'scripts' => $scripts,
	);
}