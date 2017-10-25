<?php

namespace CsrDelft\view\formulier\keuzevelden;
/**
 * GeslachtField.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Man of vrouw
 */
class GeslachtField extends RadioField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, array('m' => 'Man', 'v' => 'Vrouw'));
	}

}
