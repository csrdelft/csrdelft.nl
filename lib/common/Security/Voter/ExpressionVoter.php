<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\common\CsrException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Checkt een rechten epxressie
 *
 * Or: `bestuur,pubcie`. Lid is bestuur of pubcie
 * And: `bestuur+pubcie`. Lid is bestuur en pubcie
 * Or: `bestuur|pubcie`. Lid is bestuur of pubcie
 *
 * `,` heeft precedence over `+` heeft precedence over `|` dus
 * `bestuur|pubcie+ROLE_FISCAAT_MOD betekent (bestuur of pubcie) en ROLE_FISCAAT_MOD
 */
class ExpressionVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	public function __construct(
		private AccessDecisionManagerInterface $accessDecisionManager
	) {
	}

	public function supportsAttribute(string $attribute): bool
	{
		return (bool) preg_match('/[|,+]/', $attribute);
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		// OR
		if (str_contains($attribute, ',')) {
			/**
			 * Het gevraagde mag een enkele permissie zijn, of meerdere, door komma's
			 * gescheiden, waarvan de gebruiker er dan een hoeft te hebben. Er kunnen
			 * dan ook uid's tussen zitten, als een daarvan gelijk is aan dat van de
			 * gebruiker heeft hij ook rechten.
			 */
			$p = explode(',', $attribute);
			$result = false;
			foreach ($p as $perm) {
				$result = $result || $this->accessDecisionManager->decide(
					$token,
					[$perm],
					$subject
				);
			}
		}
		// AND
		elseif (str_contains($attribute, '+')) {
			/**
			 * Gecombineerde permissie:
			 * gebruiker moet alle permissies bezitten
			 */
			$p = explode('+', $attribute);
			$result = true;
			foreach ($p as $perm) {
				$result = $result && $this->accessDecisionManager->decide(
					$token,
					[$perm],
					$subject
				);
			}
		}
		// OR (secondary)
		elseif (str_contains($attribute, '|')) {
			/**
			 * Mogelijkheid voor OR binnen een AND
			 * Hierdoor zijn er geen haakjes nodig in de syntax voor niet al te ingewikkelde statements.
			 * Statements waarbij haakjes wel nodig zijn moet je niet willen.
			 */
			$p = explode('|', $attribute);
			$result = false;
			foreach ($p as $perm) {
				$result =  $result || $this->accessDecisionManager->decide(
					$token,
					[$perm],
					$subject
				);
			}
		} else {
			throw new CsrException('Rechten expressie bevat geen |,+');
		}

		return $result;
	}
}
