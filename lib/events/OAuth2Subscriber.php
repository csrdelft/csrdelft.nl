<?php

namespace CsrDelft\events;

use CsrDelft\common\Security\OAuth2Scope;
use CsrDelft\common\Util\CryptoUtil;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\RememberOAuthRepository;
use CsrDelft\service\AccessService;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;
use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\Event\ScopeResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class OAuth2Subscriber implements EventSubscriberInterface
{
	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly Environment $twig,
		private readonly Security $security,
		private readonly AccessService $accessService,
		private readonly RememberOAuthRepository $rememberOAuthRepository,
		private readonly AccountRepository $accountRepository
	) {
	}

	public static function getSubscribedEvents(): array
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
			$requestedScopes = array_map(
				fn($scope) => new Scope($scope),
				(array) $request->query->get('scopeChoice')
			);
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
			$request->getSession()->set('token', CryptoUtil::uniqid_safe('token_'));
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
			if ($this->security->isGranted(OAuth2Scope::magScope($scope))) {
				$scopeBeschrijving[] = [
					'naam' => $scope->__toString(),
					'beschrijving' => OAuth2Scope::getBeschrijving($scope),
					'optioneel' => OAuth2Scope::isOptioneel($scope),
				];
			}
		}

		$redirect_uri = parse_url((string) $request->get('redirect_uri'));

		$redirect_uri_formatted =
			$redirect_uri['host'] .
			(isset($redirect_uri['port']) ? ':' . $redirect_uri['port'] : '');

		$response = new Response(
			$this->twig->render('oauth2/authorize.html.twig', [
				'client_id' => $event->getClient()->getIdentifier(),
				'redirect_uri' => $request->get('redirect_uri'),
				'redirect_uri_formatted' => $redirect_uri_formatted,
				'response_type' => $request->get('response_type'),
				'token' => $request->getSession()->get('token'),
				'state' => $request->get('state'),
				'scope' => $request->get('scope'),
				'scopes' => $scopeBeschrijving,
			]),
			200,
			[]
		);

		$event->setResponse($response);
	}
}
