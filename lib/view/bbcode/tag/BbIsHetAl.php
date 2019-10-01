<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\view\IsHetAlView;

class BbIsHetAl extends BbTag {

	public static function getTagName() {
		return 'ishetal';
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []) {
		$this->readMainArgument($arguments);
		if ($this->content == '') {
			$this->content = lid_instelling('zijbalk', 'ishetal');
		}
	}

	public function render() {
		ob_start();
		echo '<div class="my-3 p-3 bg-white rounded shadow-sm">';
		(new IsHetAlView($this->content))->view();
		echo '</div>';
		return ob_get_clean();
	}
}
