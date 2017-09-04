<?php
namespace CsrDelft\model\entity\mededelingen;
use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * MededelingAccess.enum.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */

abstract class MededelingAccess extends PersistentEnum {

    const Post = 'P_NEWS_POST';
    const Mod = 'P_NEWS_MOD';

    public static function getTypeOptions() {
        return array(self::Post, self::Mod);
    }

    public static function getDescription($option) {
        switch ($option) {
            case self::Post: return 'Ouderejaarskring';
            case self::Mod: return 'Eerstejaarskring';
            default: throw new CsrException('Toegang onbekend');
        }
    }

    public static function getChar($option) {
        switch ($option) {
            case self::Post: return 'P';
            case self::Mod: return 'M';
            default: throw new CsrException('Toegang onbekend');
        }
    }

}
