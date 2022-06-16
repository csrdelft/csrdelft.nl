<?php

namespace CsrDelft\view\formulier\knoppen;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class ResetKnop extends FormulierKnop
{
	public function __construct(
		$url = null,
		$action = 'reset',
		$label = 'Reset',
		$title = 'Reset naar opgeslagen gegevens',
		$icon = 'undo'
	) {
		parent::__construct($url, $action, $label, $title, $icon);
	}
}
