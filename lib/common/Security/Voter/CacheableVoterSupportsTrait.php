<?php

namespace CsrDelft\common\Security\Voter;

trait CacheableVoterSupportsTrait
{
	abstract function supportsAttribute(string $attribute);
	abstract function supportsType(string $subjectType);
	protected function supports(string $attribute, $subject): bool
	{
		return $this->supportsAttribute($attribute) &&
			$this->supportsType($subject ? $subject::class : '');
	}
}
