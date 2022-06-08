<?php

namespace CsrDelft\events;

use CsrDelft\common\Security\OAuth2Scope;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\RememberOAuthRepository;
use CsrDelft\service\AccessService;
use CsrDelft\service\security\LoginService;
use Nyholm\Psr7\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\ScopeResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class OAuth2Subscriber implements EventSubscriberInterface
{
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var LoginService
	 */
	private $loginService;
	/**
	 * @var AccessService
	 */
	private $accessService;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var RememberOAuthRepository
	 */
	private $rememberOAuthRepository;

	public function __construct(
		RequestStack $requestStack,
		Environment $twig,
		LoginService $loginService,
		AccessService $accessService,
		RememberOAuthRepository $rememberOAuthRepository,
		AccountRepository $accountRepository
	) {
		$this->loginService = $loginService;
		$this->requestStack = $requestStack;
		$this->twig = $twig;
		$this->accessService = $accessService;
		$this->accountRepository = $accountRepository;
		$this->rememberOAuthRepository = $rememberOAuthRepository;
	}

	public static function getSubscribedEvents()
	{
		return [
			OAuth2Events::SCOPE_RESOLVE => 'onScopeResolve',
			OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'onAuthorizationRequest',
		];
	}

	public function onScopeResolve(ScopeResolveEvent $event)
	{
		$rememberOAuth = $this->rememberOAuthRepository->findByUser(
			$event->getUserIdentifier(),
			$event->getClient()->getIdentifier()
		);
		$user = $this->accountRepository->find($event->getUserIdentifier());

		if ($rememberOAuth) {
			$rememberOAuth->lastUsed = date_create_immutable();
			$rememberedScopes = explode(' ', $rememberOAuth->scopes);

			$scopes = [];
			foreach ($event->getScopes() as $scope) {
				if (
					in_array((string) $scope, $rememberedScopes) &&
					$this->accessService->isUserGranted(
						$user,
						OAuth2Scope::magScope($scope)
					)
				) {
					$scopes[] = $scope;
				}
			}

			$event->setScopes(...$scopes);
			return;
		}

		$requestedScopes = $event->getScopes();

		$request = $this->requestStack->getMainRequest();

		if ($request->query->has('scopeChoice')) {
			$requestedScopes = array_map(function ($scope) {
				return new Scope($scope);
			}, (array) $request->query->get('scopeChoice'));
		}

		$scopes = [];
		foreach ($requestedScopes as $scope) {
			if (
				$this->accessService->isUserGranted(
					$user,
					OAuth2Scope::magScope($scope)
				)
			) {
				$scopes[] = $scope;
			}
		}

		$event->setScopes(...$scopes);
	}

	/**
	 * @param AuthorizationRequestResolveEvent $event
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function onAuthorizationRequest(
		AuthorizationRequestResolveEvent $event
	): void {
		$request = $this->requestStack->getMainRequest();

		$rememberOAuth = $this->rememberOAuthRepository->findByUser(
			$event->getUser()->getUserIdentifier(),
			$event->getClient()->getIdentifier()
		);

		if ($rememberOAuth) {
			$rememberOAuth->lastUsed = date_create_immutable();

			$event->resolveAuthorization(
				AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED
			);
			return;
		}

		// Maak een tijdelijke token aan om te voorkomen dat een applicatie voor de gebruiker kan approven.
		if (!$request->getSession()->has('token')) {
			$request->getSession()->set('token', uniqid_safe('token_'));
		}

		if ($request->get('cancel')) {
			$event->resolveAuthorization(
				AuthorizationRequestResolveEvent::AUTHORIZATION_DENIED
			);
			return;
		}

		if ($request->get('token') == $request->getSession()->get('token')) {
			if ($request->get('remember')) {
				// Vinkje bij vertrouw applicatie

				$this->rememberOAuthRepository->nieuw(
					$event->getUser(),
					$event->getClient()->getIdentifier(),
					$event->getScopes()
				);
			}

			$event->resolveAuthorization(
				AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED
			);
			return;
		}

		/** @var Scope[] $requestedScopes */
		$requestedScopes = array_unique(
			array_merge($event->getScopes(), $event->getClient()->getScopes())
		);

		// Deze check wordt ook gedaan in OAuth2ScopeSubscriber
		$scopeBeschrijving = [];
		foreach ($requestedScopes as $scope) {
			if ($this->loginService->_mag(OAuth2Scope::magScope($scope))) {
				$scopeBeschrijving[] = [
					'naam' => $scope->__toString(),
					'beschrijving' => OAuth2Scope::getBeschrijving($scope),
					'optioneel' => OAuth2Scope::isOptioneel($scope),
				];
			}
		}

		$redirect_uri = parse_url($request->get('redirect_uri'));

		$redirect_uri_formatted =
			$redirect_uri['host'] .
			(isset($redirect_uri['port']) ? ':' . $redirect_uri['port'] : '');

		$response = new Response(
			200,
			[],
			$this->twig->render('oauth2/authorize.html.twig', [
				'client_id' => $event->getClient()->getIdentifier(),
				'redirect_uri' => $request->get('redirect_uri'),
				'redirect_uri_formatted' => $redirect_uri_formatted,
				'response_type' => $request->get('response_type'),
				'token' => $request->getSession()->get('token'),
				'state' => $request->get('state'),
				'scope' => $request->get('scope'),
				'scopes' => $scopeBeschrijving,
			])
		);

		$event->setResponse($response);
	}
}
