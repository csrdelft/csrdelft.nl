<?php

namespace CsrDelft\view\formulier\knoppen;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class SubmitKnop extends FormulierKnop
{

	public function __construct($url = null, $action = 'submit', $label = 'Opslaan', $title = 'Invoer opslaan', $icon = 'disk')
	{
		parent::__construct($url, $action, $label, $title, $icon);
	}

}
