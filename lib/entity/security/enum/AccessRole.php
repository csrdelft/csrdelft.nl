<?php

namespace CsrDelft\entity\security\enum;

use CsrDelft\common\Enum;
use CsrDelft\repository\security\AccessRepository;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * RBAC MAC roles.
 *
 * @see AccessRepository
 */
class AccessRole extends Enum
{
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
	 * Extra rechtenset voor Forum Moderators.
	 * Een combinatie van normaal lid en P_FORUM_MOD.
	 */
	const ForumModerator = 'R_FORUM_MOD';

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
		self::ForumModerator => 'ForumModerator-rechten',
	];

	/**
	 * @param string $from
	 * @return string[]
	 */
	public static function canChangeAccessRoleTo($from)
	{
		if ($from === self::PubCie) {
			return static::getEnumValues();
		} elseif ($from === self::Bestuur) {
			return [self::Nobody, self::Eter, self::Oudlid, self::Lid];
		} else {
			return [];
		}
	}
}
