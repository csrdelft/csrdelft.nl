<?php

namespace CsrDelft\view\formulier\elementen;
/**
 * FieldSet.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Je moet zelf de fieldset sluiten!
 */
class FieldSet extends HtmlComment {

	public function getHtml() {
		return '<fieldset><legend>' . $this->comment . '</legend>';
	}

}
