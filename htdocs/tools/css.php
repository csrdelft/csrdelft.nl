<?php
/**
 * C.S.R. stylesheet creator based on DokuWiki StyleSheet creator
 *
 * @author     Gerrit Uitslag <klapinklapin@gmail.com>
 */
if (!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../wiki/').'/');

//reuse the CSS functions of DokuWiki without triggering the main function css_out()
define('SIMPLE_TEST', 1);
require_once(DOKU_INC . 'lib/exe/css.php');

/**
 *  TODO LET OP:  DokuCssFileCSR extends DokuCssFile uit wiki/lib/exe/css.php,
 *  AANGEPAST: alle 'private' declaraties moeten 'protected' worden in: DokuCssFile
 *  @see DokuCssFile
 *
 *  Helper class to abstract loading of css/less files
 *
 *  @author Chris Smith <chris@jalakai.co.uk>
 */
class DokuCssFileCSR extends DokuCssFile {

	/**
	 * TODO LET OP Dit is een kopie van getRelative(), zodat relatieve pad op onze manier kunnen bepalen (2 mods)
	 * @see getRelativePath()
	 *
	 * Get the relative file system path of this file, relative to dokuwiki's root folder, DOKU_INC
	 *
	 * @return string   relative file system path
	 */
	public function createRelativePath(){

		if (is_null($this->relative_path)) {
			//$basedir = array(DOKU_INC);
			//mod: een map omhoog vanaf /[absolute path]/htdocs/wiki/ naar /[absolute path]/htdocs/
			$basedir = array(realpath(DOKU_INC. '..').'/');

			// during testing, files may be found relative to a second base dir, TMP_DIR
			if (defined('DOKU_UNITTEST')) {
				$basedir[] = realpath(TMP_DIR);
			}

			$basedir = array_map('preg_quote_cb', $basedir);
			$regex = '/^('.join('|',$basedir).')/';
			$this->relative_path = preg_replace($regex, '', dirname($this->filepath));

			//mod: creëer een relatief pad t.o.v. DOKU_INC, welke door less-parser zal worden gebruikt
			$this->relative_path = '../' . $this->relative_path;
		}

		$this->relative_path;
	}

}

// overrule sommige instellingen, zie voor uitleg op https://www.dokuwiki.org/config
$conf['compress'] = DEBUG ? 0 : 1; //stripping of whitespace and comments
$conf['cssdatauri'] = 0; //filesize in bytes. Embed images below the thresshold in css. (Bad supported by IE < 8)
$conf['allowdebug'] = 0;
$conf['cachedir'] = DATA_PATH . 'compressorcache';
$conf['cachetime'] = 100 * 60 * 60 * 24; // -1, 0, ..

// foutmeldingen van de css/less-parser voor de header-tag plakken
$attachcsswarningbefore = 'header';
/**
 *
 * TODO: na wiki-update dit terugzetten in: wiki/lib/exe/css.php op ongeveer lijn 206
 *
global $attachcsswarningbefore;
if(!isset($attachcsswarningbefore)) $attachcsswarningbefore = '.dokuwiki';

echo "$attachcsswarningbefore:before {
            content: '$error';
            background-color: red;
            display: block;
            background-color: #fcc;
            border-color: #ebb;
            color: #000;
            padding: 0.5em;
        }";
 *
 *
 */


//generate css file
header('Content-Type: text/css; charset=utf-8');
csr_css_out();

// ---------------------- C.S.R. functions ------------------------------

/**
 * Output all needed Styles
 *
 * @see css_out() Based on css_out()
 */
function csr_css_out() {
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
	list(/* $timestamp */, $cache_ok, $modules,	$files,	$cache,	$replacements) = CompressedLayout::checkCache($layout, $selectedmodule, 'css');

	// handle conditional request, based on cache state
	// This may exit if a cache can be used
	http_cached($cache->cache, $cache_ok);

	// start output buffering
	ob_start();

	// build the stylesheet
	foreach ($modules as $module) {
		// load files
		$css_content = '';
		foreach ($files[$module] as $file => $location) {
			$display = str_replace(fullpath(HTDOCS_PATH), '', fullpath($file));
			$css_content .= "\n/* XXXXXXXXX $display XXXXXXXXX */\n";
			$css_content .= csr_css_loadfile($file, $location);
		}

		print NL . $css_content . NL;
	}

	// end output buffering and get contents
	$css = ob_get_contents();
	ob_end_clean();

	// strip any source maps
	stripsourcemaps($css);

	// apply style replacements
	$css = css_applystyle($css, $replacements);

	// parse less
	$css = css_parseless($css);

	// compress whitespace and comments
	if ($conf['compress']) {
		$css = css_compress($css);
	}

	// embed small images right into the stylesheet
	if ($conf['cssdatauri']) {
		$base = preg_quote(DOKU_BASE, '#');
		$css = preg_replace_callback('#(url\([ \'"]*)(' . $base . ')(.*?(?:\.(png|gif)))#i', 'css_datauri', $css);
	}

	http_cached_finish($cache->cache, $css);
}


/**
 * COPY van wiki/lib/exe/css.php, AANGEPAST: gebruikt DokuCssFileCSR
 *
 * Loads a given file and fixes relative URLs with the
 * given location prefix
 */
function csr_css_loadfile($file,$location=''){
	$css_file = new DokuCssFileCSR($file);
	$css_file->createRelativePath();
	return $css_file->load($location);
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
function css_csr_styleini($layout) {
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
		if (is_array($data['replacements'])) {
			$replacements = array_merge($replacements, css_fixreplacementurls($data['replacements'], $webbase));
		}
	}

	return array(
		'stylesheets' => $stylesheets,
		'replacements' => $replacements
	);
}