<?php


namespace CsrDelft\entity\security\enum;


use CsrDelft\common\Enum;

/**
 * Class RemoteLoginStatus
 * @package CsrDelft\entity\security\enum
 * @method static static PENDING()
 * @method static static ACTIVE()
 * @method static static ACCEPTED()
 * @method static static REJECTED()
 * @method static static EXPIRED()
 */
class RemoteLoginStatus extends Enum
{
	const PENDING = 'pending';
	const ACTIVE = 'active';
	const ACCEPTED = 'accepted';
	const REJECTED = 'rejected';
	const EXPIRED = 'expired';

	protected static $mapChoiceToDescription = [
		self::PENDING => self::PENDING,
		self::ACTIVE => self::ACTIVE,
		self::ACCEPTED => self::ACCEPTED,
		self::REJECTED => self::REJECTED,
		self::EXPIRED => self::EXPIRED,
	];
}
