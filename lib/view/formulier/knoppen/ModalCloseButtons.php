<?php

namespace CsrDelft\view\formulier\knoppen;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class ModalCloseButtons extends FormKnoppen {

	public $close_top;
	public $close_bottom;

	public function __construct() {
		parent::__construct();
		$this->close_bottom = new FormulierKnop(null, 'cancel', 'Sluiten', 'Venster sluiten', null);
		$this->addKnop($this->close_bottom);
	}

}
