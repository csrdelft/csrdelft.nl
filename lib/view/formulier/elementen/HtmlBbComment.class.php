<?php
namespace CsrDelft\view\formulier\elementen;
use CsrDelft\view\bbcode\CsrBB;

/**
 * HtmlBbComment.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Html en eventuele bbcode wordt geparsed.
 */
class HtmlBbComment extends HtmlComment {

	public function getHtml() {
		return CsrBB::parseHtml($this->comment, true);
	}

}