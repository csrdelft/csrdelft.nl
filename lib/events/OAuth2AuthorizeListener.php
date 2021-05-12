<?php


namespace CsrDelft\events;


use CsrDelft\common\Security\OAuth2Scope;
use Nyholm\Psr7\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class OAuth2AuthorizeListener
{
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(
		RequestStack $requestStack,
		Environment $twig
	)
	{
		$this->requestStack = $requestStack;
		$this->twig = $twig;
	}

	/**
	 * @param AuthorizationRequestResolveEvent $event
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void
	{
		$request = $this->requestStack->getMasterRequest();

		// Maak een tijdelijke token aan om te voorkomen dat een applicatie voor de gebruiker kan approven.
		if (!$request->getSession()->has('token')) {
			$request->getSession()->set('token', uniqid_safe('token_'));
		}

		if ($request->get('cancel')) {
			$event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_DENIED);
			return;
		}

		if ($request->get('token') == $request->getSession()->get('token')) {
			$event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
			return;
		}

		$allScopes = array_unique(array_merge($event->getScopes(), $event->getClient()->getScopes()));

		$scopes = array_map(function ($scope) {
			return OAuth2Scope::getBeschrijving((string)$scope);
		}, $allScopes);

		$response = new Response(200,
			[],
			$this->twig->render('oauth2/authorize.html.twig', [
				'client_id' => $event->getClient()->getIdentifier(),
				'redirect_uri' => $request->get('redirect_uri'),
				'response_type' => $request->get('response_type'),
				'token' => $request->getSession()->get('token'),
				'state' => $request->get('state'),
				'scope' => $request->get('scope'),
				'scopes' => $scopes,
			])
		);

		$event->setResponse($response);
	}
}
