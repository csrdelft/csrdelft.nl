<?php

namespace CsrDelft\entity\groepen\enum;


use CsrDelft\common\Enum;

/**
 * GroepTab.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De verschillende tabbladen om een groep weer te geven.
 *
 * @method static static Lijst
 * @method static static Pasfotos
 * @method static static Statistiek
 * @method static static Emails
 * @method static static Eetwens
 * @method static boolean isLijst($tab)
 * @method static boolean isPasfotos($tab)
 * @method static boolean isStatistiek($tab)
 * @method static boolean isEmails($tab)
 * @method static boolean isEetwens($tab)
 */
class GroepTab extends Enum
{

    /**
     * GroepTab opties.
     */
    const Lijst = 'lijst';
    const Pasfotos = 'pasfotos';
    const Statistiek = 'stats';
    const Emails = 'emails';
    const Eetwens = 'eetwens';

    /**
     * @var string[]
     */
    protected static $mapChoiceToDescription = [
        self::Lijst => 'Lijst',
        self::Pasfotos => 'Pasfoto\'s',
        self::Statistiek => 'Statistiek',
        self::Emails => 'E-mails',
        self::Eetwens => 'Allergie/dieet',
    ];

    /**
     * @var string[]
     */
    protected static $mapChoiceToChar = [
        self::Lijst => 'l',
        self::Pasfotos => 'p',
        self::Statistiek => 's',
        self::Emails => 'e',
        self::Eetwens => 'a',
    ];
}
