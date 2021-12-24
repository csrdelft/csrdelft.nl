<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\security\RememberLogin;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\login\RememberLoginForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\RememberMe\PersistentRememberMeHandler;
use Symfony\Component\Security\Http\RememberMe\PersistentTokenBasedRememberMeServices;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class SessionController extends AbstractController
{
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;

	public function __construct(RememberLoginRepository $rememberLoginRepository)
	{
		$this->rememberLoginRepository = $rememberLoginRepository;
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/rememberdata", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function rememberdata()
	{
		return $this->tableData($this->rememberLoginRepository->findBy(['uid' => $this->getUid()]));
	}

	/**
	 * @param Request $request
	 * @param PersistentRememberMeHandler $rememberMeHandler
	 * @return RememberLoginForm|Response
	 * @Route("/session/remember", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remember(Request $request, PersistentRememberMeHandler $rememberMeHandler)
	{
		$selection = $this->getDataTableSelection();

		if (empty($selection)) {
			$response = new Response();

			$request->request->set('_remember_me', true);
			$rememberMeHandler->createRememberMeCookie($this->getUser());

			return $response;
		}

		$remember = $this->rememberLoginRepository->retrieveByUUID($selection[0]);

		if (!$remember || $remember->uid !== $this->getUid()) {
			throw $this->createAccessDeniedException();
		}
		$form = new RememberLoginForm($remember);
		if ($form->validate()) {
			if (isset($_POST['DataTableId'])) {
				$response = $this->tableData([$remember]);
			} else if (!empty($_POST['redirect'])) {
				$response = new JsonResponse($_POST['redirect']);
			} else {
				$response = new JsonResponse($this->generateUrl('default'));
			}

			$this->getDoctrine()->getManager()->persist($remember);
			$this->getDoctrine()->getManager()->flush();

			return $response;
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/forget-all", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function forgetAll()
	{
		$remembers = $this->rememberLoginRepository->findBy(['uid' => $this->getUid()]);

		$response = [];
		$manager = $this->getDoctrine()->getManager();
		foreach ($remembers as $remember) {
			$response[] = new RemoveDataTableEntry($remember->id, RememberLogin::class);
			$manager->remove($remember);
		}
		$manager->flush();

		return $this->tableData($response);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/forget", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function forget()
	{
		$selection = $this->getDataTableSelection();
		if (!$selection) {
			throw $this->createAccessDeniedException();
		}
		$response = [];
		$manager = $this->getDoctrine()->getManager();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginRepository->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== $this->getUid()) {
				throw $this->createAccessDeniedException();
			}
			$response[] = new RemoveDataTableEntry($remember->id, RememberLogin::class);
			$manager->remove($remember);
		}
		$manager->flush();
		return $this->tableData($response);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/oauth2-refresh-token", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function oauth2Data()
	{
		$accessTokens = $this->getDoctrine()->getRepository(AccessToken::class)
			->findBy(['userIdentifier' => $this->getUser()->uid]);

		$refreshTokens = [];

		foreach ($accessTokens as $accessToken) {
			$refreshToken = $this->getDoctrine()->getRepository(RefreshToken::class)
				->findOneBy(['accessToken' => $accessToken->getIdentifier()]);
			if ($refreshToken) {
				$refreshTokens[] = $refreshToken;
			}
		}

		return $this->tableData(array_map(function (RefreshToken $token) {
			return [
				'UUID' => $token->getIdentifier() . '@RefreshToken.csrdelft.nl',
				'identifier' => $token->getIdentifier(),
				'client' => $token->getAccessToken()->getClient()->getIdentifier(),
				'expiry' => $token->getExpiry(),
				'revoked' => $token->isRevoked(),
			];
		}, $refreshTokens));
	}

	/**
	 * @Route("/session/oauth2-refresh-token-revoke/{identifier}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @param RefreshToken $refreshToken
	 * @return GenericDataTableResponse
	 */
	public function oauth2RefreshTokenRevoke(RefreshToken $refreshToken)
	{
		$refreshToken->revoke();
		$refreshToken->getAccessToken()->revoke();

		$this->getDoctrine()->getManager()->flush();

		return $this->tableData([[
			'UUID' => $refreshToken->getIdentifier() . '@RefreshToken.csrdelft.nl',
			'identifier' => $refreshToken->getIdentifier(),
			'client' => $refreshToken->getAccessToken()->getClient()->getIdentifier(),
			'expiry' => $refreshToken->getExpiry(),
			'revoked' => $refreshToken->isRevoked(),
		]]);
	}
}
