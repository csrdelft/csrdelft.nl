<?php

namespace CsrDelft\view\bbcode;
use function CsrDelft\email_like;

/**
 * Class BasicCsrBB.
 *
 * Alle niet stek-specifieke BB definities.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BasicCsrBB extends Parser {
	/**
	 * Subscript
	 *
	 * @example [sub]Subscript[/sub]
	 */
	function bb_sub() {
		return '<sub class="bb-tag-sub">' . $this->parseArray(array('[/sub]'), array('sub', 'sup')) . '</sub>';
	}

	/**
	 * Superscript
	 *
	 * @example [sup]Superscript[/sup]
	 */
	function bb_sup() {
		return '<sup class="bb-tag-sup">' . $this->parseArray(array('[/sup]'), array('sub', 'sup')) . '</sup>';
	}

	/**
	 * Italic
	 *
	 * @example [i]Italic[/i]
	 */
	function bb_i() {
		return '<em class="cursief bb-tag-i">' . $this->parseArray(array('[/i]'), array('i')) . '</em>';
	}

	/**
	 * Strike through
	 *
	 * @example [s]Strike through[/s]
	 */
	function bb_s() {
		return '<del class="doorgestreept bb-tag-s">' . $this->parseArray(array('[/s]'), array('s')) . '</del>';
	}

	/**
	 * Underline
	 *
	 * @example [u]Underline[/u]
	 */
	function bb_u() {
		return '<ins class="onderstreept bb-tag-u">' . $this->parseArray(array('[/u]'), array('u')) . '</ins>';
	}

	/**
	 * @var bool
	 */
	protected $noBold = false;

	/**
	 * Bold
	 *
	 * [b]Bold[/b]
	 */
	function bb_b() {
		if ($this->noBold === true AND $this->quote_level == 0) {
			return $this->parseArray(array('[/b]'), array('b'));
		} else {
			return '<strong class="dikgedrukt bb-tag-b">' . $this->parseArray(array('[/b]'), array('b')) . '</strong>';
		}
	}

	/**
	 * Disable the [b] tag
	 */
	function bb_nobold()
	{
		$this->noBold = true;
		$return = $this->parseArray(array('[/nobold]'), array());
		$this->noBold = false;

		return $return;
	}

	/**
	 * Newline
	 *
	 * @example [rn]
	 */
	function bb_rn() {
		return '<br />';
	}

	/**
	 * Horizontal line
	 *
	 * @example [hr]
	 */
	function bb_hr() {
		return '<hr class="bb-tag-hr" />';
	}

	/**
	 * CSS clear
	 *
	 * @param array $arguments
	 *
	 * @return string
	 */
	function bb_clear($arguments = array()) {
		$sClear = 'clear';
		if (isset($arguments['clear']) AND ($arguments['clear'] === 'left' OR $arguments['clear'] === 'right')) {
			$sClear .= '-' . $arguments['clear'];
		}
		return '<div class="' . $sClear . '"></div>';
	}

	/**
	 * Div
	 *
	 * @param optional String $arguments['class'] Class attribute
	 * @param optional Boolean $arguments['clear'] CSS clear: both
	 * @param optional String $arguments['float'] CSS float left or right
	 * @param optional Integer $arguments['w'] CSS width in pixels
	 * @param optional Integer $arguments['h'] CSS height in pixels
	 *
	 * @example [div class=special clear float=left w=20 h=50]...[/div]
	 */
	function bb_div($arguments = array()) {
		$content = $this->parseArray(array('[/div]'), array());
		$class = '';
		if (isset($arguments['class'])) {
			$class .= htmlspecialchars($arguments['class']);
		}
		if (isset($arguments['clear'])) {
			$class .= ' clear';
		} elseif (isset($arguments['float']) AND $arguments['float'] == 'left') {
			$class .= ' float-left';
		} elseif (isset($arguments['float']) AND $arguments['float'] == 'right') {
			$class .= ' float-right';
		}
		if ($class != '') {
			$class = ' class="bb-tag-div ' . $class . '"';
		}
		$style = '';
		if (isset($arguments['w'])) {
			$style .= 'width: ' . ((int)$arguments['w']) . 'px; ';
		}
		if (isset($arguments['h'])) {
			$style .= 'height: ' . ((int)$arguments['h']) . 'px; ';
		}
		if ($style != '') {
			$style = ' style="' . $style . '" ';
		}
		return '<div' . $class . $style . '>' . $content . '</div>';
	}

	/**
	 * Heading
	 *
	 * @param Integer $arguments ['h'] Heading level (1-6)
	 * @param optional String $arguments['id'] ID attribute
	 *
	 * @example [h=1 id=special]Heading[/h]
	 */
	function bb_h($arguments) {
		$id = '';
		if (isset($arguments['id'])) {
			$id = ' id="' . htmlspecialchars($arguments['id']) . '"';
		}
		$h = 1;
		if (isset($arguments['h'])) {
			$h = (int)$arguments['h'];
		}
		$text = '<h' . $h . $id . ' class="bb-tag-h">';
		$text .= $this->parseArray(array('[/h]'), array('h'));
		$text .= '</h' . $h . '>' . "\n\n";

		// remove trailing br (or even two)
		$next_tag = array_shift($this->parseArray);

		if ($next_tag != '[br]') {
			array_unshift($this->parseArray, $next_tag);
		} else {
			$next_tag = array_shift($this->parseArray);
			if ($next_tag != '[br]') {
				array_unshift($this->parseArray, $next_tag);
			}
		}
		return $text;
	}


	/**
	 * Code
	 *
	 * @param optional String $arguments['code'] Description of code type
	 *
	 * @example [code=PHP]phpinfo();[/code]
	 */
	function bb_code($arguments = array()) {
		$content = $this->parseArray(array('[/code]'), array('code', 'br', 'all' => 'all'));

		$code = '';
		if (isset($arguments['code'])) {
			$code = $arguments['code'] . ' ';
		}

		return '<div class="bb-tag-code"><sub>' . $code . 'code:</sub><pre class="bbcode">' . $content . '</pre></div>';;
	}

	/**
	 * Quote
	 *
	 * @example [quote]Citaat[/quote]
	 */
	function bb_quote() {
		if ($this->quote_level == 0) {
			$this->quote_level = 1;
			$content = $this->parseArray(array('[/quote]'), array());
			$this->quote_level = 0;
		} else {
			$this->quote_level++;
			$delcontent = $this->parseArray(array('[/quote]'), array());
			$this->quote_level--;
			unset($delcontent);
			$content = '...';
		}
		$text = '<div class="citaatContainer bb-tag-quote"><strong>Citaat</strong>' .
			'<div class="citaat">' . $content . '</div></div>';
		return $text;
	}

	/**
	 * List
	 *
	 * @param optional String $arguments['list'] Type of ordered list
	 *
	 * @example [list]Unordered list[/list]
	 * @example [ulist]Unordered list[/ulist]
	 * @example [list=a]Ordered list numbered with lowercase letters[/list]
	 */
	function bb_list($arguments) {
		$content = $this->parseArray(array('[/list]', '[/ulist]'), array('br'));
		if (!isset($arguments['list'])) {
			$text = '<ul class="bb-tag-list">' . $content . '</ul>';
		} else {
			$text = '<ol class="bb-tag-list" type="' . $arguments['list'] . '">' . $content . '</ol>';
		}
		return $text;
	}

	/**
	 * List item (short)
	 *
	 * @example [lishort]First item
	 * @example [*]Next item
	 */
	function bb_lishort() {
		return '<li class="bb-tag-li">' . $this->parseArray(array('[br]')) . '</li>';
	}

	/**
	 * List item
	 *
	 * @example [li]Item[/li]
	 */
	function bb_li() {
		return '<li class="bb-tag-li">' . $this->parseArray(array('[/li]')) . '</li>';
	}

	/**
	 * Slash me
	 *
	 * @param optional String $arguments['me'] Name of who is me
	 *
	 * @example [me] waves
	 * @example [me=Name] waves
	 */
	function bb_me($arguments) {
		$content = $this->parseArray(array('[br]'), array());
		array_unshift($this->parseArray, '[br]');
		if (isset($arguments['me'])) {
			$html = '<span style="color:red;">* ' . $arguments['me'] . $content . '</span>';
		} else {
			$html = '<span style="color:red;">/me' . $content . '</span>';
		}
		return $html;
	}

	/**
	 * UBB off
	 *
	 * @example [ubboff]Not parsed[/ubboff]
	 * @example [tekst]Not parsed[/tekst]
	 */
	function bb_ubboff() {
		$this->bb_mode = false;
		$content = $this->parseArray(array('[/ubboff]', '[/tekst]'), array());
		$this->bb_mode = true;
		return $content;
	}

	/**
	 * Email anchor
	 *
	 * @param String $arguments ['email'] Email address to link to
	 * @param optional Boolean $arguments['spamsafe'] Uses spam safe javascript obfuscator
	 *
	 * @example [email]noreply@csrdelft.nl[/email]
	 * @example [email=noreply@csrdelft.nl spamsafe]text[/email]
	 */
	function bb_email($arguments) {
		$mailto = array_shift($this->parseArray);
		$endtag = array_shift($this->parseArray);

		$email = '';
		$text = '';

		// only valid patterns
		if ($endtag == '[/email]') {
			if (isset($arguments['email'])) {
				if (email_like($arguments['email'])) {
					$email = $arguments['email'];
					$text = $mailto;
				}
			} else {
				if (email_like($mailto)) {
					$email = $text = $mailto;
				}
			}
		} else {
			if (isset($arguments['email'])) {
				if (email_like($arguments['email'])) {
					$email = $text = $arguments['email'];
				}
			}
			array_unshift($this->parseArray, $endtag);
			array_unshift($this->parseArray, $mailto);
		}
		if (!empty($email)) {
			$html = '<a class="bb-tag-email" href="mailto:' . $email . '">' . $text . '</a>';

			//spamprotectie: rot13 de email-tags, en voeg javascript toe om dat weer terug te rot13-en.
			if (isset($arguments['spamsafe'])) {
				$html = '<script>document.write("' . str_rot13(addslashes($html)) . '".replace(/[a-zA-Z]/g, function(c){ return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));</script>';
			}
		} else {
			$html = '[email] Ongeldig e-mailadres (' . htmlspecialchars($mailto) . ')';
		}
		return $html;
	}

	/**
	 * Table
	 *
	 * @param optional String $arguments['border'] CSS border style
	 * @param optional String $arguments['color'] CSS color style
	 * @param optional String $arguments['background-color'] CSS background-color style
	 * @param optional String $arguments['border-collapse'] CSS border-collapse style
	 *
	 * @example [table border=1px_solid_blue]...[/table]
	 */
	function bb_table($arguments) {
		$tableProperties = array('border', 'color', 'background-color', 'border-collapse');
		$style = '';
		foreach ($arguments as $name => $value) {
			if (in_array($name, $tableProperties)) {
				$style .= $name . ': ' . str_replace('_', ' ', htmlspecialchars($value)) . '; ';
			}
		}

		$content = $this->parseArray(array('[/table]'), array('br'));
		$html = '<table class="bb-table bb-tag-table" style="' . $style . '">' . $content . '</table>';
		return $html;
	}

	/**
	 * Table row
	 *
	 * @example [tr]...
	 * @example [tr]...[/tr]
	 */
	function bb_tr() {
		$content = $this->parseArray(array('[/tr]'), array('br'));
		$html = '<tr class="bb-tag-tr">' . $content . '</tr>';
		return $html;
	}

	/**
	 * Table cell
	 *
	 * @param optional Integer $arguments['w'] CSS width in pixels
	 *
	 * @example [td w=50]...[/td]
	 */
	function bb_td($arguments = array()) {
		$content = $this->parseArray(array('[/td]'), array());

		$style = '';
		if (isset($arguments['w'])) {
			$style .= 'width: ' . (int)$arguments['w'] . 'px; ';
		}

		$html = '<td class="bb-tag-td" style="' . $style . '">' . $content . '</td>';
		return $html;
	}

	/**
	 * Table header cell
	 *
	 * @example [th]...[/th]
	 */
	function bb_th() {
		$content = $this->parseArray(array('[/th]'), array());
		$html = '<th class="bb-tag-th">' . $content . '</th>';
		return $html;
	}
}
