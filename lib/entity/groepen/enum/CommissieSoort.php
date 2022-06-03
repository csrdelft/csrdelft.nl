<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * (Bestuurs-)Commissie / SjaarCie.
 *
 * @method static static Commissie
 * @method static static SjaarCie
 * @method static static BestuursCommissie
 * @method static static Extern
 */
class CommissieSoort extends Enum
{
    /**
     * Commissie soorten.
     */
    const Commissie = 'c';
    const SjaarCie = 's';
    const BestuursCommissie = 'b';
    const Extern = 'e';

    /**
     * @var string[]
     */
    protected static $mapChoiceToDescription = [
        self::Commissie => 'Commissie',
        self::SjaarCie => 'SjaarCie',
        self::BestuursCommissie => 'Bestuurscommissie',
        self::Extern => 'Externe commissie',
    ];
}
