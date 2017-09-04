<?php
namespace CsrDelft\model\entity\groepen;
use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * KetzerSelectorSoort.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De keuzesoort van een selector: AND (Multiple) / XOR (Single)
 *
 */
abstract class KetzerSelectorSoort extends PersistentEnum {

	const Single = 'XOR';
	const Multiple = 'AND';

	public static function values() {
		return array(self::Single, self::Multiple);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Single: return 'Keuzerondje';
			case self::Multiple: return 'Vinkje';
			default: throw new CsrException('KetzerSelectorSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Single:
			case self::Multiple:
				return $option;
			default: throw new CsrException('KetzerSelectorSoort onbekend');
		}
	}

}
