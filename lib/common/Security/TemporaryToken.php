<?php


namespace CsrDelft\common\Security;


use CsrDelft\entity\security\Account;
use CsrDelft\service\security\SuService;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Tijdelijke Token. Om acties uit te kunnen voeren als een gebruiker tijdens een request.
 *
 * @see SuService
 * @package CsrDelft\common\Security
 */
class TemporaryToken extends AbstractToken
{
	/**
	 * @var TokenInterface
	 */
	private $originalToken;

	public function __construct(Account $account, TokenInterface $originalToken)
	{
		parent::__construct($account->getRoles());

		$this->setUser($account);
		$this->setAuthenticated(true);

		$this->originalToken = $originalToken;
	}

	public function getOriginalToken()
	{
		return $this->originalToken;
	}

	public function getCredentials()
	{
		return '';
	}
}
