<?php
namespace CsrDelft\view\bbcode;

use function CsrDelft\email_like;
use function CsrDelft\url_like;

/**
 * Main BB-code Parser file
 *
 * This file is based on the eamBBParser class of the eamBBParser project.
 *
 * @package eamBBParser
 * @author Erik Bakker <erik@eamelink.nl>
 * @copyright 2005, Erik Bakker <erik@eamelink.nl>
 * @license http://www.gnu.org/copyleft/lesser.html
 */

class Parser {

	/**
	 * Storage for BB code
	 */
	private $bbcode;

	/**
	 * Storage for outgoing HTML
	 */
	private $HTML;

	/**
	 * Array holding tags & text
	 *
	 * An array, like this ->
	 * Array (
	 * 		[0] => Hello, this is
	 * 		[1] => [b]
	 * 		[2] => bold
	 * 		[3] => [/b]
	 * 		[4] => , cool huh?!
	 * 		  )
	 */
	protected $parseArray = array();

	/**
	 * Aliasses, like * for lishort
	 */
	private $aliassen = array(
		'*'		 => 'lishort',
		'ulist'	 => 'list'
	);

	/**
	 * The current quote level, to make sure nested quotes are replaced by ...
	 */
	protected $quote_level = 0;

	/**
	 * How deep are we; e.g. How many open tags?
	 */
	private $level = 0;

	/**
	 * Amount of tags already parsed
	 */
	private $tags_counted = 0;

	/**
	 * Maximum allowed number of tags
	 *
	 * When having trouble with users trying to get down the server by using tons of tags, lower this limit. Open and closing tags will count towards max.
	 * @var int Maximum number of tags to be parsed.
	 *
	 */
	public $max_tags = 700;

	/**
	 * Allow HTML in code
	 *
	 * Whether or not to allow HTMl code. Default is false. When set to false, [html] tag won't do anything.
	 * @var boolean
	 */
	public $allow_html = false;

	/**
	 * Accept html by default
	 *
	 * When set to true, html will be accepted. When set to false, only html within [html] tags will be accepted. This setting only has effect when $allow_html is set to true.
	 * @var boolean
	 */
	public $standard_html = false;

	/**
	 * Enable paragraph mode
	 *
	 * When set to true, the parser will try to use &lt;p&gt; tags around text, and remove unnecessary br's. <b>This is an experimental feature! Please let me know if you find strange behaviour!</b>
	 * @var boolean
	 */
	protected $paragraph_mode = true;

	/**
	 * Keep track of open paragraphs
	 */
	private $paragraph_open = false;

	/**
	 * Tags that do not need to be encapsulated in paragraphs
	 */
	private $paragraphless_tags = array('h', 'quote', 'hr', 'table', 'br');

	/**
	 * Keep track of current paragraph-required-status
	 *
	 * When we're in a tag that does not need to be encapsulate in a paragraph, this var will be false, otherwise true. Works only when in paragraph mode.
	 */
	private $paragraph_required = true;

	/**
	 * It's possible with the ubboff tag to switch processing off.
	 *
	 * Keep track of ubb status
	 *
	 * When we're in a [ubboff] block, this will be false. Otherwise true.
	 * Also used by [commentaar] and [prive]
	 */
	protected $bb_mode = true;

	/**
	 * Transform BB code to HTML code.
	 *
	 * This method takes a text with BB code and transforms it to HTML.
	 * @param string $bbcode BB code to be transformed
	 * @return string HTML
	 */
	public function getHtml($bbcode) {
		if (strlen($bbcode) == 0) {
			return null;
		}

		$this->bbcode = str_replace(array("\r\n", "\n"), '[br]', $bbcode);

		// Create the parsearray with the buildarray function, pretty nice ;)
		$this->tags_counted = 0;
		$this->parseArray = $this->buildArray($this->bbcode);

		// Fix html rights
		$this->htmlFix();

		// Get output
		$this->HTML = str_replace('[br]', "<br />\n", $this->parseArray());

		return $this->HTML;
	}

	/**
	 * Set [html] and [nohtml] tags according to settings
	 */
	private function htmlFix() {
		// First, check if html is allowed
		if (!$this->allow_html) {
			$html = false;
		} elseif ($this->standard_html) {
			$html = true;
		} else {
			$html = false;
		}

		$nohtmltagopen = $htmltagopen = false;

		$newParseArray = array();
		while ($tag = array_shift($this->parseArray)) {
			switch ($tag) {
				case '[nohtml]':
				case '[/html]':
					$html = false;
					break;
				case '[html]':
				case '[/nohtml]':
					if ($this->allow_html) {
						$html = true;
					}
					break;

				default :

					if ($html) {

						if ($tag == '[br]') {
							$tag = "\n";
						} // Really, no BR's in html code is wanted.
						$newParseArray[] = $tag;
					} else {

						$newParseArray[] = htmlspecialchars($tag);
					}
			}
		}
		$this->parseArray = $newParseArray;
		return true;
	}

	/**
	 * Breaks the inputted BB code into an array of text and tags
	 */
	private function buildArray($string) {

		if (strlen($string) == 0) // Empty or no string
			return false;

		$opensign = strpos($string, '[');
		$nextopensign = strpos($string, '[', $opensign + 1);
		$closesign = strpos($string, ']');

		// if there are no more opensigns, or closesigns, or if the closesign is on position 0
		if ($opensign === false || $closesign == false)
			return Array($string);

		// Check max tags limit
		if ($this->tags_counted >= $this->max_tags) {
			return Array('<b style="color:red">[max # of tags reached, quitting splitting procedure]</b>' . $string);
		}

		// Nothing's been found yet ;)
		$found = false;

		while (!$found) {

			if ($closesign > $opensign && $closesign < $nextopensign) { // Parfait
				$found = true;
			} elseif ($closesign > $opensign) { // Maar er komt er nog een opensign
				$opensign = $nextopensign;
			} else { // Close is na open!
				while ($closesign < $opensign) {
					$closesign = strpos($string, ']', $closesign + 1);
				}
			}

			$nextopensign = strpos($string, '[', $opensign + 1);
			if ($nextopensign === false) { // No more opensigns, stop looping
				$found = true;
			}
		}

		$tag = substr($string, $opensign, $closesign - $opensign + 1);
		$pretext = substr($string, 0, $opensign);

		$current = Array($tag);

		// Only non [br] tags must count toward max tag limit.
		if ($tag != '[br]') {
			$this->tags_counted++;
		}

		if (!empty($pretext))
			array_unshift($current, $pretext);

		$rec_arr = $this->buildArray(substr($string, $closesign + 1));
		if (!is_array($rec_arr)) {
			$rec_arr = Array($rec_arr);
		}
		return array_merge($current, $rec_arr);
	}

	/**
	 * Process array
	 *
	 * Walks through the array until one of the stoppers is found. When encountering an 'open' tag, which is not in $forbidden, open corresponding bb_ function.
	 */
	protected function parseArray($stoppers = array(), $forbidden = array()) {

		if (!is_array($this->parseArray)) { // Well, nothing to parse
			return null;
		}
		$text = '';

		$forbidden_aantal_open = 0;
		while ($entry = array_shift($this->parseArray)) {

			if (in_array($entry, $stoppers)) {

				if ($forbidden_aantal_open == 0) {

					$this->level--;
					return $text;
				} else {
					$forbidden_aantal_open--;
					$text .= $entry;
				}
			} elseif ($this->bb_mode && $entry == '[/]') { // [ubboff] cannot be switched off with this tag.
				if ($this->level >= 1) {
					$this->level--;

					return $text;
				} else {
					// Weird, [/] while nothing is open...
				}
			} else {

				$tag = $this->getTag($entry);

				if ($tag && in_array($tag, $forbidden)) {
					if ($tag != 'br') {
						$forbidden_aantal_open++;
					} else {
						$entry = "\n";
					}
				}

				if ($this->bb_mode && substr($entry, 0, 1) == '[' && substr($entry, strlen($entry) - 1, 1) == ']' && substr($entry, 1, 1) != '/' && (method_exists($this, 'bb_' . $tag) || (isset($this->aliassen[$tag]) && method_exists($this, 'bb_' . $this->aliassen[$tag]))) && !in_array($tag, $forbidden) && !isset($forbidden['all'])) {
					$functionname = 'bb_' . $tag;
					if (!method_exists($this, $functionname))
						$functionname = 'bb_' . $this->aliassen[$tag];

					$arguments = $this->getArguments($entry);

					if ($this->paragraph_mode) {
						// Add paragraphs if necessary

						$paragraph_setting_modified = false;
						if (!$this->paragraph_open && !in_array($tag, $this->paragraphless_tags) && $this->level == 0) {  // Only encaps when level = 0, we don't want paragraphs inside lists or stuff
							$text .= '<p>';
							$this->paragraph_open = true;
						} elseif (in_array($tag, $this->paragraphless_tags)) {
							// We're in some tag that doesn't need to be <p> enclosed, like a heading or a table.
							if ($this->paragraph_required) {
								$paragraph_setting_modified = true;
								$this->paragraph_required = false;
							}
							if ($this->paragraph_open && $this->level == 0) {

								$text .= "</p>\n\n";
								$this->paragraph_open = false;
							}
						}
					}

					$this->level++;
					$newtext = $this->$functionname($arguments);

					// Reset paragraph_required.
					if ($this->paragraph_mode && $paragraph_setting_modified)
						$this->paragraph_required = true;

					$text .= $newtext;
				} else {

					if ($this->paragraph_mode && $entry == '[br]') {

						$shift = array_shift($this->parseArray);
						if ($shift == '[br]') {
							// Two brs, looks like a new paragraph!
							// First, check for more. We don't want endless <p></p> pairs, that doesn't work.

							$secondshift = array_shift($this->parseArray);
							while ($secondshift == '[br]') {
								$secondshift = array_shift($this->parseArray);
								$text .= "<br 2/>\n";
							}
							array_unshift($this->parseArray, $secondshift);

							$shift = array_shift($this->parseArray);

							if ($this->paragraph_required && !in_array($this->getTag($shift), $this->paragraphless_tags)) {
								if ($this->paragraph_open) {
									if ($this->level == 0) {
										// Close old one, and open new one
										$entry = "</p>\n\n<p>";
										$this->paragraph_open = true;
									}
								} else {
									if ($this->level == 0) {
										$entry = "<p 1>";
										$this->paragraph_open = true;
									}
								}
							} else {
								$entry = null;
							}

							// We have found 1 [br], so normally we'd put it back
							// But if next thing is a paragraphless tag (say, table) or end of document,
							// We can skip the [br], since there will be a </p> anyway.
						} elseif (in_array($this->getTag($shift), $this->paragraphless_tags)) {

							$entry = null;
						} else {

						}
						array_unshift($this->parseArray, $shift);
					}

					// Add paragraphs if necessary
					if ($this->paragraph_mode && !$this->paragraph_open && $this->paragraph_required && $this->level == 0) {
						$text .= '<p>';
						$this->paragraph_open = true;
					}

					$text .= $entry;
				}
			}
		} // End of BIG while!

		if ($this->paragraph_open) { // No need for a level check, should be zero anyway.
			$this->paragraph_open = false;
			$text .= '</p>';
		}

		return $text;
	}

	/**
	 * return name of a tag
	 *
	 * When supplied with a full tag ([b] or [img w=5 h=10]), return tag name
	 * @return string
	 * @param string $fullTag The full tag to get the tagname from
	 */
	private function getTag($fullTag) {
		if (substr($fullTag, 0, 1) == '[' && substr($fullTag, strlen($fullTag) - 1, 1) == ']') {
			return strtok($fullTag, '[ =]');
		} else {
			return false;
		}
	}

	/**
	 * return arguments of a tag in array-form
	 *
	 * When supplied with a full tag ([h=5] or [img=blah.gif w=5 h=10]), return array with argument/value as key/value pairs
	 * @return array
	 * @param string $fullTag The full tag to get the arguments from
	 */
	private function getArguments($fullTag) {

		$argument_array = Array();
		$tag = substr($fullTag, 1, strlen($fullTag) - 2);
		$argList = explode(' ', $tag);
		$i = 0;
		foreach ($argList as $entry) {
			$split = explode('=', $entry);
			if (count($split) >= 2) {
				$key = array_shift($split);
				$value = implode('=', $split);
			} else {
				if ($i != 0) { // Do not key = val if key = tagname. Dan gewoon geen args.
					$key = $entry;
					$value = $entry;
				}
			}
			if (isset($value)) {
				// FIXME: stupid javascript filtering detected
				if (strstr(strtolower($value), 'javascript:')) {
					$value = 'disabled';
				}
				//	if(strstr(strtolower($value), '(')){
				//		$value = 'disabled';
				//	}

				$argument_array[$key] = $value;
			}
		}
		return $argument_array;
	}

	/**
	 * Heading
	 *
	 * @param Integer $arguments['h'] Heading level (1-6)
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
			$h = (int) $arguments['h'];
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

	protected $nobold = false;

	function bb_nobold($arguments = array()) {
		$this->nobold = true;
		$return = $this->parseArray(array('[/nobold]'), array());
		$this->nobold = false;

		return $return;
	}

	/**
	 * Bold
	 *
	 * @example [b]Bold[/b]
	 */
	function bb_b() {
		if ($this->nobold === true AND $this->quote_level == 0) {
			return $this->parseArray(array('[/b]'), array('b'));
		} else {
			return '<strong class="bb-tag-b">' . $this->parseArray(array('[/b]'), array('b')) . '</strong>';
		}
	}

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
		return '<em class="bb-tag-i">' . $this->parseArray(array('[/i]'), array('i')) . '</em>';
	}

	/**
	 * Strikethrough
	 *
	 * @example [s]Strikethrough[/s]
	 */
	function bb_s() {
		return '<span style="text-decoration: line-through;" class="bb-tag-s">' . $this->parseArray(array('[/s]'), array('s')) . '</span>';
	}

	/**
	 * Underline
	 *
	 * @example [u]Underline[/u]
	 */
	function bb_u() {
		return '<span style="text-decoration: underline;" class="bb-tag-u">' . $this->parseArray(array('[/u]'), array('u')) . '</span>';
	}

	/**
	 * URL anchor
	 *
	 * @param String $arguments['url'] URL it links to
	 *
	 * @example [url=https://csrdelft.nl]Anchor[/url]
	 * @example [url]https://csrdelft.nl[/url]
	 * @example [rul=https://csrdelft.nl]Anchor[/rul]
	 * @example [rul]https://csrdelft.nl[/rul]
	 */
	function bb_url($arguments = array()) {

		$content = $this->parseArray(array('[/url]', '[/rul]'), array());
		//[url=
		if (isset($arguments['url'])) {
			$href = $arguments['url'];
		} elseif (isset($arguments['rul'])) {
			$href = $arguments['rul'];
		} else {
			//of [url][/url]
			$href = $content;
		}

		// only valid patterns
		if (!url_like(urldecode($href))) {
			$text = $href;
		} else {
			$text = '<a class="bb-tag-url" href="' . $href . '">' . $content . '</a>';
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
			$code = $arguments['code'] + ' ';
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
	 * Horizontal line
	 *
	 * @example [hr]
	 */
	function bb_hr($arguments) {
		return '<hr class="bb-tag-hr" />';
	}

	/**
	 * List item (short)
	 *
	 * @example [lishort]First item
	 * @example [*]Next item
	 */
	function bb_lishort($arguments) {
		return '<li class="bb-tag-li">' . $this->parseArray(array('[br]')) . '</li>';
	}

	/**
	 * List item
	 *
	 * @example [li]Item[/li]
	 */
	function bb_li($arguments) {
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
	 * @param String $arguments['email'] Email address to link to
	 * @param optional Boolean $arguments['spamsafe'] Uses spam safe javascript obfuscator
	 *
	 * @example [email]noreply@csrdelft.nl[/email]
	 * @example [email=noreply@csrdelft.nl spamsafe]text[/email]
	 */
	function bb_email($arguments) {
		$html = '';

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
	 * Image
	 *
	 * @example [img]URL[/img]
	 */
	function bb_img($arguments) {
		$content = $this->parseArray(array('[/img]', '[/IMG]'), array());

		// only valid patterns
		if (!url_like(urldecode($content))) {
			$html = $content;
		} else {
			//als de html toegestaan is hebben we genoeg vertrouwen om sommige karakters niet te encoderen
			if (!$this->allow_html) {
				$content = htmlspecialchars($content);
			}
			$html = '<img class="bb-img bb-tag-img" src="' . $content . '" alt="' . $content . '" />';
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
				$style.=$name . ': ' . str_replace('_', ' ', htmlspecialchars($value)) . '; ';
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
			$style.='width: ' . (int) $arguments['w'] . 'px; ';
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
			$class.=' clear';
		} elseif (isset($arguments['float']) AND $arguments['float'] == 'left') {
			$class.=' float-left';
		} elseif (isset($arguments['float']) AND $arguments['float'] == 'right') {
			$class.=' float-right';
		}
		if ($class != '') {
			$class = ' class="bb-tag-div ' . $class . '"';
		}
		$style = '';
		if (isset($arguments['w'])) {
			$style.='width: ' . ((int) $arguments['w']) . 'px; ';
		}
		if (isset($arguments['h'])) {
			$style.='height: ' . ((int) $arguments['h']) . 'px; ';
		}
		if ($style != '') {
			$style = ' style="' . $style . '" ';
		}
		return '<div' . $class . $style . '>' . $content . '</div>';
	}

}
