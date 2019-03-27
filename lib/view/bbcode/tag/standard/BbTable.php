<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Table
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param string optional $arguments['border'] CSS border style
 * @param string optional $arguments['color'] CSS color style
 * @param string optional $arguments['background-color'] CSS background-color style
 * @param string optional $arguments['border-collapse'] CSS border-collapse style
 *
 * @example [table border=1px_solid_blue]...[/table]
 */
class BbTable extends BbTag {

	public function getTagName() {
		return 'table';
	}

	public function parse($arguments = []) {
		$tableProperties = array('border', 'color', 'background-color', 'border-collapse');
		$style = '';
		foreach ($arguments as $name => $value) {
			if (in_array($name, $tableProperties)) {
				$style .= $name . ': ' . str_replace('_', ' ', htmlspecialchars($value)) . '; ';
			}
		}

		return '<table class="bb-table bb-tag-table" style="' . $style . '">' . $this->getContent(['br']) . '</table>';
	}

	public function isParagraphLess() {
		return true;
	}
}
