<?php

namespace CsrDelft\entity\forum;

use CsrDelft\common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-16
 * @method static static NOOIT
 * @method static static VERMELDING
 * @method static static ALTIJD
 * @method static boolean isNOOIT($niveau)
 * @method static boolean isVERMELDING($niveau)
 * @method static boolean isALTIJD($niveau)
 */
class ForumDraadMeldingNiveau extends Enum
{

    const NOOIT = 'nooit';
    const VERMELDING = 'vermelding';
    const ALTIJD = 'altijd';

    /**
     * @var string[]
     */
    protected static $mapChoiceToDescription = [
        self::NOOIT => 'Nooit',
        self::VERMELDING => 'Bij vermelding',
        self::ALTIJD => 'Altijd'
    ];
}
