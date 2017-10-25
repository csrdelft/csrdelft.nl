<?php

namespace CsrDelft\view\agenda;

/**
 * Requires id of deleted agenda item.
 */
class AgendaItemDeleteView extends AgendaView {

	public function view() {
		echo '<div id="item-' . $this->model . '" class="remove"></div>';
	}

}
