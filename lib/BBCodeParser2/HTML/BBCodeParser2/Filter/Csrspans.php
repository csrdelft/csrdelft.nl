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
			'htmlopen'  => 'span class="doorgestreept"',
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

		'l337' => array(
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

		'reldate' => array(
			'allowed'   => 'none',
			'attributes'=> array(),
			'plugin'    => 'Csrspans'),
		'neuzen' => array(
			'allowed'   => 'all',
			'attributes'=> array(),
			'plugin'    => 'Csrspans'),
		'instelling' => array(
			'allowed'   => 'all',
			'attributes'=> array(),
			'plugin'    => 'Csrspans'),
		'prive' => array(
			'allowed'   => 'all',
			'attributes'=> array(),
			'plugin'    => 'Csrspans'),
	);

	/**
	 * [instelling] en [prive] kunnen output uitzetten
	 *
	 * @var bool
	 */
	protected $outputdisabled = false;

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
	protected function html_l337(array $tag, &$enabled) {
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
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_reldate(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['reldate'])) {
					$content = trim($tag['attributes']['reldate']);
				} else {
					$content = '';
				}

				return '<span title="' . htmlspecialchars($content) . '">' . reldate($content) . '</span>';
		}
		return false;
	}

	/**
	 * Vervang neuzen in tekst
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_neuzen(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 0:
				$content = $tag['text'];

				if (LidInstellingen::get('layout', 'neuzen') != 'nee') {
					$neus = '<img src="//csrdelft.nl/plaetjes/famfamfam/bullet_red.png" alt="o" class="neus2013" />';
					$content = str_replace('o', $neus, $content);
				}
				return $content;
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
	 * Toont content als instelling een bepaalde waarde heeft,
	 * standaard 'ja';
	 *
	 * [instelling=maaltijdblokje module=voorpagina][maaltijd=next][/instelling]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_instelling(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				$arguments = $tag['attributes'];

				if (isset($arguments['instelling'])) {
					$arguments['instelling'] = trim($arguments['instelling']);
				} else {
					return 'Geen of een niet bestaande instelling opgegeven: ' . htmlspecialchars($arguments['instelling']);
				}

				if (!isset($arguments['module'])) { // backwards compatibility
					$key = explode('_', $arguments['instelling'], 2);
					$arguments['module'] = $key[0];
					$arguments['instelling'] = $key[1];
				}

				$testwaarde = 'ja';
				if (isset($arguments['waarde'])) {
					$testwaarde = $arguments['waarde'];
				}

				try {
					if (LidInstellingen::get($arguments['module'], $arguments['instelling']) == $testwaarde) {
						$enabled = 'disable_output';
						$this->outputdisabled = true;
					}
					return '';
				} catch (Exception $e) {
					return '[instelling]: ' . $e->getMessage();
				}

			case 2:
				if ($this->outputdisabled) {
					$enabled = 'enable_output';
					$this->outputdisabled = false;
				}

				return '';
		}
		return false;
	}

	/**
	 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
	 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_prive(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['prive'])) {
					$permissie = trim($tag['attributes']['prive']);
				} else {
					$permissie = 'P_LOGGED_IN';
				}

				$forbidden = !LoginModel::mag($permissie);

				if ($forbidden) {
					$enabled = 'disable_output';
					$this->outputdisabled = true;
				}
				return '';

			case 2:
				if ($this->outputdisabled) {
					$enabled = 'enable_output';
					$this->outputdisabled = false;
				}
				return '';
		}
		return false;
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

		$pattern = array(	"#".$oe."hr".$ce."(?!".$oe."/hr".$ce.")#Ui",   	// [hr] zonder [/hr] erachter
							"#".$oe."(/?)1337".$ce."#U");                   // [/?1337] no both case match
		$replace = array(   $o."hr".$c.$o."/hr".$c,                         // [hr][/hr]
							$o."\$1l337".$c);                               // [/?l337]
		$this->_preparsed = preg_replace($pattern, $replace, $this->_text);
	}

}
