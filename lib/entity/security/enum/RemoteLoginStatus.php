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
 */
class RemoteLoginStatus extends Enum
{
	const PENDING = 'pending';
	const ACTIVE = 'active';
	const ACCEPTED = 'accepted';
	const REJECTED = 'rejected';
}
