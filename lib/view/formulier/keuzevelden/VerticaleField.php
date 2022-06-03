<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\groepen\VerticalenRepository;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Selecteer een verticale. Geeft een volgnummer terug.
 */
class VerticaleField extends SelectField
{

    public function __construct($name, $value, $description)
    {
        $verticalen = array();
        foreach (ContainerFacade::getContainer()->get(VerticalenRepository::class)->findAll() as $v) {
            $verticalen[$v->letter] = $v->naam;
        }
        parent::__construct($name, $value, $description, $verticalen);
    }

}
