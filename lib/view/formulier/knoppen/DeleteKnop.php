<?php

namespace CsrDelft\view\formulier\knoppen;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class DeleteKnop extends FormulierKnop
{

    public function __construct($url, $action = 'post confirm redirect', $label = 'Verwijderen', $title = 'Definitief verwijderen', $icon = 'cross')
    {
        parent::__construct($url, $action, $label, $title, $icon);
    }

}
