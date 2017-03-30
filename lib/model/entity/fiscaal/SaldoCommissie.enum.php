<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * SaldoCommissie.enum.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
abstract class SaldoCommissie implements PersistentEnum {

    const SOCCIE = 'soccie';
    const MAALCIE = 'maalcie';

    public static function getTypeOptions() {
        return array(self::SOCCIE, self::MAALCIE);
    }

    public static function getDescription($option) {
        switch ($option) {
            case self::SOCCIE: return 'soccie';
            case self::MAALCIE: return 'maalcie';
            default: throw new Exception('Commissie onbekend');
        }
    }

    public static function getChar($option) {
        switch ($option) {
            case self::SOCCIE: return 'S';
            case self::MAALCIE: return 'M';
            default: throw new Exception('Commissie onbekend');
        }
    }

}
