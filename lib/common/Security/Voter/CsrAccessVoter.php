<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\service\AccessService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CsrAccessVoter extends Voter
{
	/**
	 * @var AccessService
	 */
	private $accessService;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security, AccessService $accessService)
	{
		$this->accessService = $accessService;
		$this->security = $security;
	}
	protected function supports(string $attribute, $subject)
	{
		if (preg_match('/^ROLE_[a-zA-Z_]+$/', $attribute)) {
			return false;
		}
		if (preg_match('/^IS_[a-zA-Z_]+$/', $attribute)) {
			return false;
		}
		return true;
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		if ($subject == null) {
			$subject = $this->security->getUser();
		}
		return $this->accessService->mag($subject, $attribute);
	}
}
