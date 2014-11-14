<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * @package  HTML_BBCodeParser2
 * @author
 */


/**
 * Filter for basic formatting
 */
class HTML_BBCodeParser2_Filter_Div extends HTML_BBCodeParser2_Filter {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @var      array
	 */
	protected $_definedTags = array(
		'div' => array(
			'plugin'     => 'Div',
			'allowed'    => 'all',
			'attributes' => array(
				'class' => '',
				'clear' => '',
				'float' => '',
				'w'     => '',
				'h'     => '')),
		'clear' => array(
			'htmlopen'         => 'div class="clear',
			'htmlopen_postfix' => '"',
			'htmlclose'        => 'div',
			'allowed'          => 'all',
			'attributes'       => array(
				'clear' => '-%1$s')),
		'spoiler' => array(
			'htmlopen'   => 'button class="spoiler">Toon verklapper</button><div class="spoiler-content"',
			'htmlclose'  => 'div',
			'allowed'    => 'all',
			'attributes' => array()),
		'verklapper' => array(
			'htmlopen'   => 'button class="spoiler">Toon verklapper</button><div class="spoiler-content"',
			'htmlclose'  => 'div',
			'allowed'    => 'all',
			'attributes' => array())

	);

	/**
	 * @param array  $tag
	 * @param string $flag (reference) provide tagname if htmltext_<tag> should be called on text between tags
	 * @return false|string html or false for using default
	 */
	protected function html_div(array $tag, &$flag) {
		switch ($tag['type']) {
			case 1:
				$attr = $tag['attributes'];
				$class = '';
				if (isset($attr['class'])) {
					$class .= htmlspecialchars($attr['class']);
				}
				if (isset($attr['clear'])) {
					$class .= ' clear';
				} elseif (isset($attr['float']) AND $attr['float'] == 'left') {
					$class .= ' float-left';
				} elseif (isset($attr['float']) AND $attr['float'] == 'right') {
					$class .= ' float-right';
				}
				if ($class != '') {
					$class = ' class="' . $class . '"';
				}
				$style = '';
				if (isset($attr['w'])) {
					$style .= 'width: ' . ((int)$attr['w']) . 'px; ';
				}
				if (isset($attr['h'])) {
					$style .= 'height: ' . ((int)$attr['h']) . 'px; ';
				}
				if ($style != '') {
					$style = ' style="' . $style . '" ';
				}
				return '<div' . $class . $style . '>';
			case 2:
				return '</div>';
		}
		return false;
	}

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

		$pattern = "#".$oe."clear(?:=(?:(left|right)|.*?))?".$ce."(?!".$oe."/clear".$ce.")#Ui";
		$replace = $o."clear=\$1".$c.$o."/clear".$c;
		$this->_preparsed = preg_replace($pattern, $replace, $this->_text);
	}
}
