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
$conf['compress'] = DEBUG ? 0 : 1; //stripping of whitespace and comments
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
	// module
	$selectedmodule = trim(preg_replace('/[^\w-]+/', '', $INPUT->str('m')));

	// The generated script depends on some dynamic options
	list(/* $timestamp */, $cache_ok, $modules,	$files,	$cache,	/* $replacements */) = HtmlPage::checkCache($layout, $selectedmodule, 'js');

	// handle conditional request, based on cache state
	// This may exit if a cache can be used
	http_cached($cache->cache, $cache_ok);

	// start output buffering and build the script
	ob_start();

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