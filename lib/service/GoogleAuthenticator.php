<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrException;
use CsrDelft\entity\GoogleToken;
use CsrDelft\repository\GoogleTokenRepository;
use CsrDelft\service\security\LoginService;
use Google_Client;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class om in te loggen met Google OAuth
 */
class GoogleAuthenticator
{
	/**
	 * @var GoogleTokenRepository
	 */
	private $googleTokenRepository;
	/**
	 * @var LoginService
	 */
	private $loginService;
	/**
	 * @var RequestStack
	 */
	private $requestStack;

	public function __construct(
		GoogleTokenRepository $googleTokenRepository,
		LoginService $loginService,
		RequestStack $requestStack
	) {
		$this->googleTokenRepository = $googleTokenRepository;
		$this->loginService = $loginService;
		$this->requestStack = $requestStack;
	}

	/**
	 * @return Google_Client
	 */
	public function createClient(): Google_Client
	{
		$request = $this->requestStack->getCurrentRequest();
		$redirect_uri = $request->getSchemeAndHttpHost() . '/google/callback';
		$client = new Google_Client();
		$client->setApplicationName('Stek');
		$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
		$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
		$client->setRedirectUri($redirect_uri);
		$client->setAccessType('offline');
		// Zonder force kunnen we nog een oude sessie krijgen (zonder refresh token)
		$client->setApprovalPrompt('force');
		$client->setScopes(['https://www.googleapis.com/auth/contacts']);

		return $client;
	}

	public function isAuthenticated(): bool
	{
		return $this->googleTokenRepository->exists($this->loginService->_getUid());
	}

	public function getToken(): GoogleToken
	{
		$token = $this->googleTokenRepository->find($this->loginService->_getUid());

		if (!$token) {
			throw new CsrException('getToken aangeroepen terwijl deze niet bestaat');
		} else {
			return $token;
		}
	}

	/**
	 * Vraag een Authsub-token aan bij google, plaats bij ontvangen in GoogleToken tabel.
	 *
	 * @param string $state Moet de url bevatten waar naar geredirect moet worden als de authenticatie gelukt is.
	 * De url zonder `addToGoogleContacts` wordt gebruikt als de authenticatie mislukt.
	 * @throws CsrException
	 */
	public function doRequestToken(string $state): void
	{
		if (!$this->isAuthenticated()) {
			$client = $this->createClient();
			$client->setState(urlencode($state));

			$googleImportUrl = $client->createAuthUrl();
			header('HTTP/1.0 307 Temporary Redirect');
			header("Location: $googleImportUrl");
			exit();
		}
	}

	public function deleteToken(): void
	{
		$token = $this->getToken();
		$this->googleTokenRepository->remove($token);
	}
}
