<?php

namespace CsrDelft\view\agenda;

/**
 * Requires id of deleted agenda item.
 */
class AgendaItemDeleteView extends AgendaView {

	public function view() {
		echo '<div id="item-' . str_replace('@', '-', str_replace('.', '-', $this->model->getUUID())) . '" class="remove"></div>';
	}

}
