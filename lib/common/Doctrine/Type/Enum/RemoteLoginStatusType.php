<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\security\enum\RemoteLoginStatus;

class RemoteLoginStatusType extends EnumType
{
	public function getEnumClass(): string
	{
		return RemoteLoginStatus::class;
	}

	public function getName(): string
	{
		return 'enumRemoteLoginStatus';
	}
}
