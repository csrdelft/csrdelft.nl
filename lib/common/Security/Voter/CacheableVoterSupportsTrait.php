<?php

namespace CsrDelft\common\Security\Voter;

trait CacheableVoterSupportsTrait
{
	protected function supports(string $attribute, $subject): bool
	{
		return $this->supportsAttribute($attribute) &&
			$this->supportsType($subject ? get_class($subject) : '');
	}
}
