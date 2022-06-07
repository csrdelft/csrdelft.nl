<?php

namespace CsrDelft\common\Security\Voter;

trait CacheableVoterTrait
{
	protected function supports(string $attribute, $subject)
	{
		return $this->supportsAttribute($attribute) &&
			$this->supportsType(get_class($subject));
	}
}
