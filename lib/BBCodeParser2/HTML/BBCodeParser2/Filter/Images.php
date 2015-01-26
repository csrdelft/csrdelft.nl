<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>                               |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
 * @package  HTML_BBCodeParser
 * @author   Stijn de Reede  <sjr@gmx.co.uk>
 */

/**
 * Filter for image tag
 */
class HTML_BBCodeParser2_Filter_Images extends HTML_BBCodeParser2_Filter {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @var      array
	 */
	protected $_definedTags = array(
		'img' => array(
//			'htmlopen'   => 'img',
//			'htmlclose'  => '',
			'allowed'	 => 'none',
			'attributes' => array(
				'img'	 => ' src=%2$s%1$s%2$s',
				'w'		 => ' width=%2$s%1$d%2$s',
				'h'		 => ' height=%2$s%1$d%2$s',
				'alt'	 => ' alt=%2$s%1$s%2$s',
				'class'	 => '',
				'float'	 => ''
			),
			'plugin'	 => 'Images'
		)
	);

	/**
	 * Executes statements before the actual array building starts
	 *
	 * This method should be overwritten in a filter if you want to do
	 * something before the parsing process starts. This can be useful to
	 * allow certain short alternative tags which then can be converted into
	 * proper tags with preg_replace() calls.
	 * The main class walks through all the filters and and calls this
	 * method if it exists. The filters should modify their private $_text
	 * variable.
	 *
	 * @see      $_text
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	protected function _preparse() {
		$options = $this->_options;
		$o = $options['open'];
		$c = $options['close'];
		$oe = $options['open_esc'];
		$ce = $options['close_esc'];

		$pattern = "!" . $oe . "img(\s?.*)" . $ce . "(.*)" . $oe . "/img" . $ce . "!Ui";
		$replace = $o . "img=\"\$2\" alt=\"\"\$1" . $c . $o . "/img" . $c;
		$this->_preparsed = preg_replace($pattern, $replace, $this->_text);
	}

	/**
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_img(array $tag, &$enabled) {

		switch ($tag['type']) {
			case 1:
				$arguments = $tag['attributes'];
				$style = '';
				$class = '';

				if (isset($arguments['img'])) {
					$url = trim($arguments['img']);
				} else {
					$url = '';
				}
				if (isset($arguments['class'])) {
					$class .= htmlspecialchars($arguments['class']);
				}
				if (isset($arguments['float'])) {
					switch ($arguments['float']) {
						case 'left':
							$class .= ' float-left';
							break;
						case 'right':
							$class .= ' float-right';
							break;
					}
				}
				if (isset($arguments['w']) AND $arguments['w'] > 10) {
					$style .= 'width: ' . ((int) $arguments['w']) . 'px; ';
				}
				if (isset($arguments['h']) AND $arguments['h'] > 10) {
					$style .= 'height: ' . ((int) $arguments['h']) . 'px;';
				}

				// only valid patterns & prevent CSRF
				if (!url_like(urldecode($url)) OR startsWith($url, CSR_ROOT)) {
					return $url;
				}
				// als de html toegestaan is hebben we genoeg vertrouwen om sommige karakters niet te encoderen
				if (!$this->_options['allowhtml']) {
					$url = htmlspecialchars($url);
				}
				// lazy loading van externe images bijv. op het forum
				if (!startsWith($url, CSR_ROOT) OR startsWith($url, CSR_ROOT . '/plaetjes/fotoalbum/')) {
					return '<div class="bb-img-loading" src="' . $url . '" title="' . htmlspecialchars($url) . '" style="' . $style . '"></div>';
				}
				return '<img class="bb-img ' . $class . '" src="' . $url . '" alt="' . $url . '" style="' . $style . '" />';
		}
		return false;
	}

}
