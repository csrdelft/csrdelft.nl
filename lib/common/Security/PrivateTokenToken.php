<?php


namespace CsrDelft\common\Security;


use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class PrivateTokenToken extends AbstractToken
{
    public function __construct(UserInterface $user, array $roles = [])
    {
        parent::__construct($roles);

        $this->setUser($user);
        $this->setAuthenticated(true);
    }

    public function getCredentials()
    {
        return '';
    }
}
