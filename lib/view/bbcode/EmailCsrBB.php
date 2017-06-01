<?php

namespace CsrDelft\view\bbcode;
use function CsrDelft\startsWith;
use function CsrDelft\url_like;

/**
 * Class EmailCsrBB.
 *
 * Doe emailspecifieke dingen met bbcode.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class EmailCsrBB extends CsrBB
{
	/**
	 * @param $bbcode
	 *
	 * @return string
	 */
	public static function parse($bbcode) {
		$parser = new EmailCsrBB();
		$parser->email_mode = true;
		return $parser->getHtml($bbcode);
	}

	/**
	 * Image
	 *
	 * @param optional String $arguments['class'] Class attribute
	 * @param optional String $arguments['float'] CSS float left or right
	 * @param optional Integer $arguments['w'] CSS width in pixels
	 * @param optional Integer $arguments['h'] CSS height in pixels
	 *
	 * @example [img class=special float=left w=20 h=50]URL[/img]
	 */
	function bb_img($arguments = array()) {
		$url = $this->parseArray(array('[/img]', '[/IMG]'), array());
		$url = filter_var($url, FILTER_SANITIZE_URL);
		if (!$url OR ( !url_like($url) AND ! startsWith($url, '/plaetjes/') )) {
			return $url;
		}
		$style = '';
		$class = '';
		if (isset($arguments['class'])) {
			$class .= htmlspecialchars($arguments['class']);
		}
		if (isset($arguments['float'])) {
			switch ($arguments['float']) {
				case 'left':
					$style .= 'float:left;';
					break;
				case 'right':
					$style .= 'float:right;';
					break;
			}
		}
		if (isset($arguments['w']) AND $arguments['w'] > 10) {
			$style .= 'width: ' . ((int)$arguments['w']) . 'px; ';
		}
		if (isset($arguments['h']) AND $arguments['h'] > 10) {
			$style .= 'height: ' . ((int)$arguments['h']) . 'px;';
		}

		return '<img class="bb-img ' . $class . '" src="' . $url . '" alt="' . htmlspecialchars($url) . '" style="' . $style . '" />';
	}

}
