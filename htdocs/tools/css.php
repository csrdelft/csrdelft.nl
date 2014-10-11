<?php
/**
 * C.S.R. stylesheet creator based on DokuWiki StyleSheet creator
 *
 * @author     Gerrit Uitslag <klapinklapin@gmail.com>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../wiki/');

//reuse the CSS dispatcher functions of DokuWiki without triggering the main function css_out()
define('SIMPLE_TEST', 1);
require_once(DOKU_INC . 'lib/exe/css.php');

// ---------------------- C.S.R. functions ------------------------------

// overrule sommige instellingen, zie voor uitleg op https://www.dokuwiki.org/config
//$conf['template'] = 'dokuwiki';
$conf['compress'] = 1;   //stripping of whitespace and comments
//$conf['cssdatauri'] = 0; //filesize in bytes. Embed images below the thresshold in css. (Bad supported by IE < 8)
//$conf['allowdebug'] = 0;
$conf['cachedir'] = DATA_PATH.'compressorcache';
//$conf['cachetime'] = 60*60*24; // -1, 0, ..


//generate css file
header('Content-Type: text/css; charset=utf-8');
csr_css_out();

/**
 * Output all needed Styles
 *
 * @see css_out() Based on css_out()
 */
function csr_css_out(){
	global $conf;
	global $INPUT;

	// decide from where to get the layout
	$layout = $INPUT->str('l');
	$allowedlayouts = array('layout', 'layout2', 'layout3');
	if(!in_array($layout, $allowedlayouts)) {
		$layout = $allowedlayouts[0];
	}

	// determine module
	$activemodule = trim(preg_replace('/[^\w-]+/','',$INPUT->str('m')));
	$excludegeneralstyles = ($INPUT->str('general') == 'no');

	if(!$activemodule) $activemodule = '';
	if($excludegeneralstyles) {
		$modules = array();
	} else {
		$modules = array('general');
	}
	$modules[] = $activemodule;

	// The generated script depends on some dynamic options
	$cache = new cache('styles'.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'].DOKU_BASE.$layout.$activemodule.$excludegeneralstyles,'.css');

	// load styl.ini
	$styleini = css_csrstyleini($layout);

	// cache influencers
	$tplinc = HTDOCS_PATH.$layout;
	$cache_files = array();
	$cache_files[] = $tplinc.'/style.ini';
	$cache_files[] = __FILE__;

	// Array of needed files and their web locations, the latter ones
	// are needed to fix relative paths in the stylesheets
	$files = array();
	foreach($modules as $module) {
		$files[$module] = array();
//		// load core styles
//		$files[$mediatype][DOKU_INC.'lib/styles/'.$mediatype.'.css'] = DOKU_BASE.'lib/styles/';
//

		// load styles
		if (isset($styleini['stylesheets'][$module])) {
			$files[$module] = array_merge($files[$module], $styleini['stylesheets'][$module]);
		}

		$cache_files = array_merge($cache_files, array_keys($files[$module]));
	}

	// check cache age & handle conditional request
	// This may exit if a cache can be used
	http_cached($cache->cache,
		$cache->useCache(array('files' => $cache_files)));

	// start output buffering
	ob_start();

	// build the stylesheet
	foreach ($modules as $module) {

//		// print the default classes for interwiki links and file downloads
//		if ($module == 'screen') {
//			print '@media screen {';
//			css_interwiki();
//			css_filetypes();
//			print '}';
//		}

		// load files
		$css_content = '';
		foreach($files[$module] as $file => $location){
			$display = str_replace(fullpath(HTDOCS_PATH), '', fullpath($file));
			$css_content .= "\n/* XXXXXXXXX $display XXXXXXXXX */\n";
			$css_content .= css_loadfile($file, $location);
		}

		print NL.$css_content.NL;

//		switch ($module) {
//			case 'screen':
//				print NL.'@media screen { /* START screen styles */'.NL.$css_content.NL.'} /* /@media END screen styles */'.NL;
//				break;
//			case 'print':
//				print NL.'@media print { /* START print styles */'.NL.$css_content.NL.'} /* /@media END print styles */'.NL;
//				break;
//			case 'all':
//			case 'feed':
//			default:
//				print NL.'/* START rest styles */ '.NL.$css_content.NL.'/* END rest styles */'.NL;
//				break;
//		}
	}
	// end output buffering and get contents
	$css = ob_get_contents();
	ob_end_clean();

	// strip any source maps
	stripsourcemaps($css);

	// apply style replacements
	$css = css_applystyle($css, $styleini['replacements']);

	// parse less
	$css = css_parseless($css);

	// compress whitespace and comments
	if($conf['compress']){
		$css = css_compress($css);
	}

	// embed small images right into the stylesheet
	if($conf['cssdatauri']){
		$base = preg_quote(DOKU_BASE,'#');
		$css = preg_replace_callback('#(url\([ \'"]*)('.$base.')(.*?(?:\.(png|gif)))#i','css_datauri',$css);
	}

	http_cached_finish($cache->cache, $css);
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
function css_csrstyleini($layout) {
	$stylesheets = array(); // mode, file => base
	$replacements = array(); // placeholder => value

	// load template's style.ini
	$incbase = HTDOCS_PATH;
	$webbase = CSR_ROOT;
	$ini = $incbase.$layout.'/style.ini';
	if(file_exists($ini)){
		$data = parse_ini_file($ini, true);

		// stylesheets
		if(is_array($data['stylesheets'])) foreach($data['stylesheets'] as $file => $module){
			$stylesheets[$module][$incbase.$file] = $webbase;
		}

		// replacements
		if(is_array($data['replacements'])){
			$replacements = array_merge($replacements, css_fixreplacementurls($data['replacements'],$webbase));
		}
	}

	return array(
		'stylesheets' => $stylesheets,
		'replacements' => $replacements
	);
}