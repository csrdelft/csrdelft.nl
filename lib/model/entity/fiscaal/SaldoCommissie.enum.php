<?php

/**
 * SaldoCommissie.enum.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
abstract class SaldoCommissie implements PersistentEnum {

    const SocCie = 'soccie';
    const MaalCie = 'maalcie';

    public static function getTypeOptions() {
        return array(self::SocCie, self::MaalCie);
    }

    public static function getDescription($option) {
        switch ($option) {
            case self::SocCie: return 'soccie';
            case self::MaalCie: return 'maalcie';
            default: throw new Exception('Commissie onbekend');
        }
    }

    public static function getChar($option) {
        switch ($option) {
            case self::SocCie: return 'S';
            case self::MaalCie: return 'M';
            default: throw new Exception('Commissie onbekend');
        }
    }

}
