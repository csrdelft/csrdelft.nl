<?php

namespace CsrDelft\service;

use CsrDelft\service\security\LoginService;
use Google_Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class om in te loggen met Google OAuth
 */
class GoogleClientManager
{
	private readonly Google_Client $client;

	/**
	 * Vraag de client op
	 */
	public function getClient(): Google_Client
	{
		return $this->client;
	}

	/**
	 * Refresh de authentication token als die niet meer geldig is. Dit maakt een redirect
	 *
	 * @param redirectURI De URL waar terug naar genavigeerd wordt als de authenticatie klaar is
	 */
	public function refreshToken(string $redirectURI): void
	{
		if (!$this->client->isAccessTokenExpired()) {
			return;
		}
		// Gebruik nonce voor de zekerheid (base64url)
		$state =
			str_replace(
				['+', '/', '='],
				['-', '_', ''],
				base64_encode(random_bytes(16))
			) .
			':' .
			$redirectURI;
		$this->client->setState($state);
		$this->requestStack->getSession()->set('google_auth_state', $state);
		$dest = $this->client->createAuthUrl();
		$response = new RedirectResponse($dest, 307);
		$response->send();
	}
}
