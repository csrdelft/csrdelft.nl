<?php

namespace CsrDelft\view\eetplan;

class EetplanHuizenData {
	public function getPrimaryKey() {
		return array('id');
	}

	public function getAttributes() {
		return array('id', 'naam', 'soort', 'eetplan');
	}
}
