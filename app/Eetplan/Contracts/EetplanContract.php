<?php
namespace App\Eetplan\Contracts;

use App\Eetplan\Models\Eetplan;
use App\Eetplan\Models\EetplanBekenden;
use App\Models\Profiel;
use CsrDelft\model\entity\groepen\Woonoord;

interface EetplanContract
{
    /**
     * @param string $avond
     * @param Profiel[] $novieten
     * @param Woonoord[] $huizen
     * @param EetplanBekenden[] $bekenden
     * @param Eetplan[] $bezochten
     * @param bool $random
     * @return Eetplan[]
     */
    function genereer($avond, array $novieten, array $huizen, array $bekenden, array $bezochten, $random = false);
}