<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Suggereert een aantal studies, doet verder geen controle op invoer.
 */
class StudieField extends SuggestieField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, 100);
		// de studies aan de TU, even prefixen met 'TU Delft - '
		$tuStudies = array_map(function ($value) {
			return "TU Delft - " . $value;
		}, ['BK', 'CT', 'ET', 'IO', 'LST', 'LR', 'MT', 'MST', 'TA', 'TB', 'TI', 'TN', 'TW', 'WB']);
		$this->suggesties = array_merge(['INHolland - ', 'Haagse Hogeschool - ', 'EURotterdam - ', 'ULeiden - '], $tuStudies);
	}

}
