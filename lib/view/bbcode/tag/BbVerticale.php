<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\common\CsrException;
use CsrDelft\model\groepen\VerticalenModel;

/**
 * Geeft een link naar de verticale.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [verticale]A[/verticale]
 * @example [verticale=A]
 */
class BbVerticale extends BbTag {

	public function getTagName() {
		return 'verticale';
	}

	public function parse($arguments = []) {
		if (isset($arguments['verticale'])) {
			$letter = $arguments['verticale'];
		} else {
			$letter = $this->getContent();
		}
		try {
			$verticale = VerticalenModel::get($letter);
			return '<a href="/verticalen#' . $verticale->letter . '">' . $verticale->naam . '</a>';
		} catch (CsrException $e) {
			return 'Verticale met letter=' . htmlspecialchars($letter) . ' bestaat niet. <a href="/verticalen">Zoeken</a>';
		}
	}
}
