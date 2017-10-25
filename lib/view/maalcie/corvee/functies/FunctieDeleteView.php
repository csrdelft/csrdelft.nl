<?php

namespace CsrDelft\view\maalcie\corvee\functies;

use CsrDelft\view\SmartyTemplateView;

/**
 * Requires id of deleted corveefunctie.
 */
class FunctieDeleteView extends SmartyTemplateView {

	public function view() {
		echo '<tr id="corveefunctie-row-' . $this->model . '" class="remove"></tr>';
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
	}

}
