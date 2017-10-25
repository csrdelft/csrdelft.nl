<?php

namespace CsrDelft\view\forum;

/**
 * Requires id of deleted forumpost.
 */
class ForumPostDeleteView extends ForumView {

	public function view() {
		echo '<tr id="forumpost-row-' . $this->model . '" class="remove"><td></td></tr>';
	}

}
