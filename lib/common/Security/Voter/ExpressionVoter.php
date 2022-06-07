<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\common\CsrException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ExpressionVoter extends Voter
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security)
	{
		$this->security = $security;
	}

	public function supportsAttribute(string $attribute): bool
	{
		return (bool) preg_match('/[|,+]/', $attribute);
	}

	protected function supports(string $attribute, $subject)
	{
		return $this->supportsAttribute($attribute);
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		// OR
		if (strpos($attribute, ',') !== false) {
			/**
			 * Het gevraagde mag een enkele permissie zijn, of meerdere, door komma's
			 * gescheiden, waarvan de gebruiker er dan een hoeft te hebben. Er kunnen
			 * dan ook uid's tussen zitten, als een daarvan gelijk is aan dat van de
			 * gebruiker heeft hij ook rechten.
			 */
			$p = explode(',', $attribute);
			$result = false;
			foreach ($p as $perm) {
				$result |= $this->security->isGranted($perm, $subject);
			}
		}
		// AND
		elseif (strpos($attribute, '+') !== false) {
			/**
			 * Gecombineerde permissie:
			 * gebruiker moet alle permissies bezitten
			 */
			$p = explode('+', $attribute);
			$result = true;
			foreach ($p as $perm) {
				$result &= $this->security->isGranted($perm, $subject);
			}
		}
		// OR (secondary)
		elseif (strpos($attribute, '|') !== false) {
			/**
			 * Mogelijkheid voor OR binnen een AND
			 * Hierdoor zijn er geen haakjes nodig in de syntax voor niet al te ingewikkelde statements.
			 * Statements waarbij haakjes wel nodig zijn moet je niet willen.
			 */
			$p = explode('|', $attribute);
			$result = false;
			foreach ($p as $perm) {
				$result |= $this->security->isGranted($perm, $subject);
			}
		}

		throw new CsrException(
			'ExpressionVoter permissie bevat geen +,| ' . $attribute
		);
	}
}
