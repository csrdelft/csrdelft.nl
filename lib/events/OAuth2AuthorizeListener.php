<?php


namespace CsrDelft\events;


use Nyholm\Psr7\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\Security\Core\Security;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Twig\Environment;

class OAuth2AuthorizeListener
{
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var SessionBagInterface
	 */
	private $session;
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(
		Security $security,
		Session $session,
		RequestStack $requestStack,
		Environment $twig
	)
	{
		$this->security = $security;
		$this->session = $session;
		$this->requestStack = $requestStack;
		$this->twig = $twig;
	}

	public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void
	{

		$request = $this->requestStack->getMasterRequest();
		if (!$this->session->has('token')) {
			$this->session->set('token', uniqid_safe('token_'));
		}

		if ($request->get('token') == $this->session->get('token')) {
			$event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);

			return;
		}

		$user = $this->security->getUser() ? $this->security->getUser()->getUsername() : "Niemand";

		$response = new Response(200,
			[],
			$this->twig->render('oauth2/authorize.html.twig', [
				'client_id' => $event->getClient()->getIdentifier(),
				'redirect_uri' => $request->get('redirect_uri'),
				'response_type' => $request->get('response_type'),
				'token' => $this->session->get('token'),
			])
//			"Hoi, ${user}. You need to accept <form>
//<input type='hidden' value='" . $request->get('client_id') . "' name='client_id'>
//<input type='hidden' value='" . $request->get('redirect_uri') . "' name='redirect_uri'>
//<input type='hidden' value='" . $request->get('response_type') . "' name='response_type'>
//<input type='hidden' value='" . $this->session->get('token') . "' name='token'>
//<input type='submit'>dignen</input>
//</form>"
		);

		$event->setResponse($response);
	}
}
