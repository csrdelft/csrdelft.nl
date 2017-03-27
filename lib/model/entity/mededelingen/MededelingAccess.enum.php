<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * MededelingAccess.enum.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */

abstract class MededelingAccess implements PersistentEnum {

    const POST = 'P_NEWS_POST';
    const MOD = 'P_NEWS_MOD';

    public static function getTypeOptions() {
        return array(self::POST, self::MOD);
    }

    public static function getDescription($option) {
        switch ($option) {
            case self::POST: return 'Ouderejaarskring';
            case self::MOD: return 'Eerstejaarskring';
            default: throw new Exception('Toegang onbekend');
        }
    }

    public static function getChar($option) {
        switch ($option) {
            case self::POST: return 'P';
            case self::MOD: return 'M';
            default: throw new Exception('Toegang onbekend');
        }
    }

}