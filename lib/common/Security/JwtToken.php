<?php


namespace CsrDelft\common\Security;


use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;
use function is_array;

class JwtToken extends AbstractToken
{
    /**
     * @var string
     */
    private $providerKey;
    /**
     * @var string
     */
    private $token;
    /**
     * @var string
     */
    private $refreshToken;

    public function __construct(UserInterface $user, string $token, ?string $refreshToken, string $firewall, array $roles = [])
    {
        parent::__construct($roles);

        $this->setUser($user);
        $this->setAuthenticated(true);
        $this->providerKey = $firewall;
        $this->token = $token;
        $this->refreshToken = $refreshToken;
    }

    public function getCredentials()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->token, $this->refreshToken, $this->providerKey, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->token, $this->refreshToken, $this->providerKey, $parentData] = $data;
        $parentData = is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }
}
