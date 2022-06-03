<?php

namespace CsrDelft\entity\pin;

use CsrDelft\common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchStatusEnum extends Enum
{
    /**
     * PinTransactieMatchStatus opties.
     */
    const STATUS_MATCH = 'match';
    const STATUS_GENEGEERD = 'verwijderd';
    const STATUS_VERKEERD_BEDRAG = 'verkeerd bedrag';
    const STATUS_MISSENDE_TRANSACTIE = 'missende transactie';
    const STATUS_MISSENDE_BESTELLING = 'missende bestelling';

    /**
     * @var string[]
     */
    protected static $mapChoiceToDescription = [
        self::STATUS_MATCH => 'Match',
        self::STATUS_GENEGEERD => 'Genegeerd',
        self::STATUS_VERKEERD_BEDRAG => 'Verkeerd bedrag',
        self::STATUS_MISSENDE_TRANSACTIE => 'Missende transactie',
        self::STATUS_MISSENDE_BESTELLING => 'Missende bestelling',
    ];
}
