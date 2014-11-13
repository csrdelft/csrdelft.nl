<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * @package  HTML_BBCodeParser2
 * @author
 */



/**
 * Filter for basic formatting
 */
class HTML_BBCodeParser2_Filter_Csrspans extends HTML_BBCodeParser2_Filter {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @var      array
	 */
	protected $_definedTags = array(
		'offtopic' => array(
			'htmlopen'  => 'span class="offtopic"',
			'htmlclose' => 'span',
			'allowed'   => 'all',
			'attributes'=> array()),
		'vanonderwerp' => array(
			'htmlopen'  => 'span class="offtopic"',
			'htmlclose' => 'span',
			'allowed'   => 'all',
			'attributes'=> array()),
		'ot' => array(
			'htmlopen'  => 'span class="offtopic"',
			'htmlclose' => 'span',
			'allowed'   => 'all',
			'attributes'=> array()),

		'b' => array(
			'htmlopen'  => 'span class="dikgedrukt"',
			'htmlclose' => 'span',
			'allowed'   => 'all',
			'attributes'=> array()),
		'i' => array(
			'htmlopen'  => 'span class="cursief"',
			'htmlclose' => 'span',
			'allowed'   => 'all',
			'attributes'=> array()),
		's' => array(
			'htmlopen'  => 'span class="doorgestreept',
			'htmlclose' => 'span',
			'allowed'   => 'all',
			'attributes'=> array()),
		'u' => array(
			'htmlopen'  => 'span class="onderstreept"',
			'htmlclose' => 'span',
			'allowed'   => 'all',
			'attributes'=> array()),

		'h' => array(                      //add 2 newlines?
			'htmlopen'  => 'h',
			'htmlclose' => 'h',
			'allowed'   => 'all',
			'attributes'=> array(
				'h'  => '%1$d',
				'id' => ' id=%2$s%1$s%2$s')),

		'hr' => array(
			'htmlopen'  => 'hr',
			'htmlclose' => '',
			'allowed'   => 'all',
			'attributes'=> array()),

		'commentaar' => array(
			'allowed'   => 'none',
			'attributes'=> array(),
			'plugin'    => 'Csrspans'),

		'1337' => array(
			'allowed'   => 'all',
			'attributes'=> array(),
			'plugin'    => 'Csrspans'),

		'code' => array(
			'htmlopen'  		=> 'br /><sub>',
			'htmlopen_postfix' 	=> 'code:</sub><pre class="bbcode"',
			'htmlclose' 		=> 'pre',
			'allowed'   		=> 'none',
			'attributes'		=> array('code' => '%1$s ')),

		'verklapper' => array(
			'htmlopen'  => 'button class="spoiler">Toon verklapper</button><div class="spoiler-content"',
			'htmlclose' => 'div',
			'allowed'   => 'all',
			'attributes'=> array()),
		'spoiler' => array(
			'htmlopen'  => 'button class="spoiler">Toon verklapper</button><div class="spoiler-content"',
			'htmlclose' => 'div',
			'allowed'   => 'all',
			'attributes'=> array()),
	);

	/**
	 * Deletes unmatched text
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_commentaar(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 0:
				//Return empty string, so text is deleted
				return '';
			case 1:
				$enabled = true;
				return '';
			case 2:
				$enabled = false;
				return '';
		}
		return false;
	}

	/**
	 * 1337 speak
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_1337(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 0:
				$html = str_replace('er ', '0r ', $tag['text']);
				$html = str_replace('you', 'j00', $html);
				$html = str_replace('elite', '1337', $html);
				$html = strtr($html, "abelostABELOST", "48310574831057");
				return $html;
			case 1:
				$enabled = true;
				return '';
			case 2:
				$enabled = false;
				return '';
		}
		return false;
	}

	/**
	 * Geef de relatieve datum terug.
	 */
	function bb_reldate($arguments = array()) {
		$content = $this->parseArray(array('[/reldate]'), array());
		return '<span title="' . htmlspecialchars($content) . '">' . reldate($content) . '</span>';
	}

	function bb_neuzen($arguments = array()) {
		if (is_array($arguments)) {
			$content = $this->parseArray(array('[/neuzen]'), array());
		} else {
			$content = $arguments;
		}
		if (LidInstellingen::get('layout', 'neuzen') != 'nee') {
			$neus = '<img src="' . CSR_PICS . '/famfamfam/bullet_red.png" alt="o" class="neus2013">';
			$content = str_replace('o', $neus, $content);
		}
		return $content;
	}

	/**
	 * Executes statements before the actual array building starts
	 */
	protected function _preparse() {
		$options = $this->_options;
		$o  = $options['open'];
		$c  = $options['close'];
		$oe = $options['open_esc'];
		$ce = $options['close_esc'];

		$pattern = array(	"#".$oe."hr".$ce."(?!".$oe."/hr".$ce.")#Ui"); 	// [hr] zonder [/hr] erachter
		$replace = array(   $o."hr".$c.$o."/hr".$c);                        // [hr][/hr]
		$this->_preparsed = preg_replace($pattern, $replace, $this->_text);
	}

}
