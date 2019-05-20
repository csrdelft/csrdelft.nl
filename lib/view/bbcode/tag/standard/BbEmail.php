<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Email anchor
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param String $arguments ['email'] Email address to link to
 * @param boolean optional $arguments['spamsafe'] Uses spam safe javascript obfuscator
 *
 * @example [email]noreply@csrdelft.nl[/email]
 * @example [email=noreply@csrdelft.nl spamsafe]text[/email]
 */
class BbEmail extends BbTag {

	public function getTagName() {
		return 'email';
	}

	public function parse($arguments = []) {
		$mailto = array_shift($this->parser->parseArray);
		$endtag = array_shift($this->parser->parseArray);

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
			array_unshift($this->parser->parseArray, $endtag);
			array_unshift($this->parser->parseArray, $mailto);
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
}
