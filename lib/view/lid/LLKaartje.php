<?php

namespace CsrDelft\view\lid;

use CsrDelft\entity\profiel\Profiel;

/**
 * Visitekaartjes, 3 op één regel.
 */
class LLKaartje extends LLWeergave
{

    public function viewHeader()
    {
        return '';
    }

    public function viewFooter()
    {
        return '';
    }

    public function viewLid(Profiel $profiel)
    {
        return $profiel->getLink('leeg');
    }

}
