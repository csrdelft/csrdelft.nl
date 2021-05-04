<?php


namespace CsrDelft\entity\security\enum;


use CsrDelft\common\Enum;

class RemoteLoginStatus extends Enum
{
	const PENDING = 'pending';
	const ACTIVE = 'active';
	const ACCEPTED = 'accepted';
	const REJECTED = 'rejected';
}
