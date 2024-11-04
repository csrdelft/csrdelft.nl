<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\security\enum\RemoteLoginStatus;

class RemoteLoginStatusType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return RemoteLoginStatus::class
	 */
	public function getEnumClass()
	{
		return RemoteLoginStatus::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumRemoteLoginStatus'
	 */
	public function getName(): string
	{
		return 'enumRemoteLoginStatus';
	}
}
