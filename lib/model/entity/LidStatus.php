<?php

namespace CsrDelft\model\entity;

use CsrDelft\common\Enum;

/**
 * LidStatus.enum.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class LidStatus extends Enum
{
    /**
     * Status voor h.t. leden.
     */
    const Noviet = 'S_NOVIET';
    const Lid = 'S_LID';
    const Gastlid = 'S_GASTLID';

    /**
     * Status voor o.t. leden.
     */
    const Oudlid = 'S_OUDLID';
    const Erelid = 'S_ERELID';

    /**
     * Status voor niet-leden.
     */
    const Overleden = 'S_OVERLEDEN';
    const Exlid = 'S_EXLID';
    const Nobody = 'S_NOBODY';
    const Commissie = 'S_CIE';
    const Kringel = 'S_KRINGEL';

    /**
     * @var string[]
     */
    protected static $lidlike = [
        self::Noviet => self::Noviet,
        self::Lid => self::Lid,
        self::Gastlid => self::Gastlid,
    ];

    /**
     * @var string[]
     */
    protected static $oudlidlike = [
        self::Oudlid => self::Oudlid,
        self::Erelid => self::Erelid,
    ];

    /**
     * @var string[]
     */
    protected static $fiscaalOudlidlike = [
        self::Oudlid => self::Oudlid,
        self::Erelid => self::Erelid,
        self::Exlid => self::Exlid,
        self::Nobody => self::Nobody,
    ];

    /**
     * @var string[]
     */
    protected static $fiscaalLidlike = [
        self::Noviet => self::Noviet,
        self::Lid => self::Lid,
        self::Gastlid => self::Gastlid,
        self::Kringel => self::Kringel,
    ];

    /**
     * @var string[]
     */
    protected static $mapChoiceToDescription = [
        self::Noviet => 'Noviet',
        self::Lid => 'Lid',
        self::Gastlid => 'Gastlid',
        self::Oudlid => 'Oudlid',
        self::Erelid => 'Erelid',
        self::Overleden => 'Overleden',
        self::Exlid => 'Ex-lid',
        self::Nobody => 'Nobody',
        self::Commissie => 'Commissie (LDAP)',
        self::Kringel => 'Kringel',
    ];

    /**
     * @var string[]
     */
    protected static $mapChoiceToChar = [
        self::Noviet => '',
        self::Lid => '',
        self::Gastlid => '',
        self::Commissie => '∈',
        self::Exlid => '∉',
        self::Nobody => '∉',
        self::Kringel => '~',
        self::Oudlid => '•',
        self::Erelid => '☀',
        self::Overleden => '✝',
    ];

    /**
     * @return string[]
     */
    public static function getLidLike()
    {
        return array_values(static::$lidlike);
    }

    /**
     * @return LidStatus[]
     */
    public static function getLidLikeObject()
    {
        return array_map(function ($val) {
            return static::from($val);
        }, static::getLidLike());
    }

    /**
     * @return string[]
     */
    public static function getOudlidLike()
    {
        return array_values(static::$oudlidlike);
    }

    /**
     * @return LidStatus[]
     */
    public static function getOudLidLikeObject()
    {
        return array_map(function ($val) {
            return static::from($val);
        }, static::getOudLidLike());
    }

    /**
     * @return string[]
     */
    public static function getFiscaalLidLike()
    {
        return array_values(static::$fiscaalLidlike);
    }

    /**
     * @return string[]
     */
    public static function getFiscaalOudlidLike()
    {
        return array_values(static::$fiscaalOudlidlike);
    }

    /**
     * @param string $option
     *
     * @return bool
     */
    public static function isLidLike($option)
    {
        return isset(static::$lidlike[$option]);
    }

    /**
     * @param string $option
     *
     * @return bool
     */
    public static function isOudlidLike($option)
    {
        return isset(static::$oudlidlike[$option]);
    }

    public function getChar()
    {
        return static::$mapChoiceToChar[$this->getValue()];
    }
}
