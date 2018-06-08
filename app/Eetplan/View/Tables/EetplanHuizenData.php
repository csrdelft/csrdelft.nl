<?php

namespace App\Eetplan\View\Tables;

class EetplanHuizenData {
	public function getPrimaryKey() {
		return ['id'];
	}

	public function getAttributes() {
		return ['id', 'naam', 'soort', 'eetplan'];
	}
}
