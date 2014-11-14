<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * @package  HTML_BBCodeParser2
 * @author
 */


/**
 * Filter for basic formatting
 */
class HTML_BBCodeParser2_Filter_Html extends HTML_BBCodeParser2_Filter {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @var      array
	 */
	protected $_definedTags = array(
		'html' => array(
			'allowed' => 'all',
			'plugin'  => 'Html')
	);

	/**
	 * Don't escape html
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text (type 1 and 2)
	 * @return false|string html or false for using default
	 */
	protected function html_html(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				$enabled = true;
				return '';
			case 2:
				$enabled = false;
				return '';
		}
		return '';
	}

}
