<?php

namespace CsrDelft\model\entity\security;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * AccessRole.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * RBAC MAC roles.
 *
 * @see AccessModel
 */
abstract class AccessRole extends PersistentEnum {

	/**
	 * AccessRole opties.
	 */
	const Nobody = 'R_NOBODY';
	const Eter = 'R_ETER';
	const Oudlid = 'R_OUDLID';
	const Lid = 'R_LID';
	const BASFCie = 'R_BASF';
	const MaalCie = 'R_MAALCIE';
	const Bestuur = 'R_BESTUUR';
	const PubCie = 'R_PUBCIE';
	const Fiscaat = 'R_FISCAAT';

	/**
	 * Extra rechtenset voor Am. de Vlieger.
	 * Een combinatie van BASFCie (archief) en MaalCie.
	 */
	const Vlieger = "R_VLIEGER";

	/**
	 * Extra rechtenset voor Forum Moderators.
	 * Een combinatie van normaal lid en P_FORUM_MOD.
	 */
	const ForumModerator = "R_FORUM_MOD";

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Nobody => self::Nobody,
		self::Eter => self::Eter,
		self::Oudlid => self::Oudlid,
		self::Lid => self::Lid,
		self::BASFCie => self::BASFCie,
		self::MaalCie => self::MaalCie,
		self::Bestuur => self::Bestuur,
		self::PubCie => self::PubCie,
		self::Fiscaat => self::Fiscaat,
		self::Vlieger => self::Vlieger,
		self::ForumModerator => self::ForumModerator,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Nobody => 'Ex-lid/Nobody',
		self::Eter => 'Eter (inlog voor abo\'s)',
		self::Oudlid => 'Oudlid',
		self::Lid => 'Lid',
		self::BASFCie => 'BASFCie-rechten',
		self::MaalCie => 'MaalCie-rechten',
		self::Bestuur => 'Bestuur-rechten',
		self::PubCie => 'PubCie-rechten',
		self::Fiscaat => 'Fiscaat-rechten',
		self::Vlieger => 'Vlieger-rechten',
		self::ForumModerator => 'ForumModerator-rechten',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Nobody => 'N',
		self::Eter => 'E',
		self::Oudlid => 'O',
		self::Lid => 'L',
		self::BASFCie => 'BASF',
		self::MaalCie => 'M',
		self::Bestuur => 'B',
		self::PubCie => 'P',
		self::Fiscaat => 'F',
		self::Vlieger => 'V',
		self::ForumModerator => 'FM',
	];

	/**
	 * @param string $from
	 * @return string[]
	 */
	public static function canChangeAccessRoleTo($from) {
		if ($from === self::PubCie) {
			return static::getTypeOptions();
		} elseif ($from === self::Bestuur) {
			return [self::Nobody, self::Eter, self::Oudlid, self::Lid];
		} else {
			return [];
		}
	}
}
