<?php

namespace CsrDelft\entity\groepen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/06/2019
 */
class GroepStatistiekDTO
{
    public $totaal;
    public $verticale;
    public $geslacht;
    public $lichting;
    public $tijd;

    public function __construct($totaal, $verticale, $geslacht, $lichting, $tijd)
    {
        $this->totaal = $totaal;
        $this->verticale = $verticale;
        $this->geslacht = $geslacht;
        $this->lichting = $lichting;
        $this->tijd = $tijd;
    }
}
