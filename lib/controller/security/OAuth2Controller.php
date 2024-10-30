<?php

namespace CsrDelft\controller\security;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\security\RememberOAuth;
use CsrDelft\repository\security\RememberOAuthRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\login\OAuth2RememberTable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use League\Bundle\OAuth2ServerBundle\Model\AccessToken;
use League\Bundle\OAuth2ServerBundle\Model\RefreshToken;

/**
 * Beheren van OAuth2 refresh tokens en vertrouwde applicaties
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class OAuth2Controller extends AbstractController
{
	/**
	 * @return GenericDataTableResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/session/oauth2-refresh-token', methods: ['POST'])]
	public function oauth2Data(ManagerRegistry $managerRegistry)
	{
		$accessTokens = $managerRegistry
			->getRepository(AccessToken::class)
			->findBy(['userIdentifier' => $this->getUser()->uid]);

		$refreshTokens = [];

		foreach ($accessTokens as $accessToken) {
			$refreshToken = $managerRegistry
				->getRepository(RefreshToken::class)
				->findOneBy(['accessToken' => $accessToken->getIdentifier()]);
			if ($refreshToken) {
				$refreshTokens[] = $refreshToken;
			}
		}

		return $this->tableData(
			array_map(
				fn(RefreshToken $token) => [
					'UUID' => $token->getIdentifier() . '@RefreshToken.csrdelft.nl',
					'identifier' => $token->getIdentifier(),
					'client' => $token
						->getAccessToken()
						->getClient()
						->getIdentifier(),
					'expiry' => $token->getExpiry(),
					'revoked' => $token->isRevoked(),
				],
				$refreshTokens
			)
		);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @param RefreshToken $refreshToken
	 * @return GenericDataTableResponse
	 */
	#[
		Route(
			path: '/session/oauth2-refresh-token-revoke/{identifier}',
			methods: ['POST']
		)
	]
	public function oauth2RefreshTokenRevoke(
		ManagerRegistry $managerRegistry,
		RefreshToken $refreshToken
	) {
		$refreshToken->revoke();
		$refreshToken->getAccessToken()->revoke();

		$managerRegistry->getManager()->flush();

		return $this->tableData([
			[
				'UUID' => $refreshToken->getIdentifier() . '@RefreshToken.csrdelft.nl',
				'identifier' => $refreshToken->getIdentifier(),
				'client' => $refreshToken
					->getAccessToken()
					->getClient()
					->getIdentifier(),
				'expiry' => $refreshToken->getExpiry(),
				'revoked' => $refreshToken->isRevoked(),
			],
		]);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @param RememberOAuthRepository $rememberOAuthRepository
	 * @return Response
	 * @throws ExceptionInterface
	 */
	#[Route(path: '/session/oauth/remember', methods: ['POST'])]
	public function oauth2RememberTokenData(
		RememberOAuthRepository $rememberOAuthRepository
	) {
		return $this->createDataTable(OAuth2RememberTable::class)->createData(
			$rememberOAuthRepository->findBy(['uid' => $this->getUid()])
		);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @param RememberOAuth $rememberOAuth
	 * @return GenericDataTableResponse
	 */
	#[Route(path: '/session/oauth/remember/{id}/delete', methods: ['POST'])]
	public function oauth2RememberDelete(
		ManagerRegistry $managerRegistry,
		RememberOAuth $rememberOAuth
	): GenericDataTableResponse {
		if ($rememberOAuth->account->getUserIdentifier() != $this->getUid()) {
			throw new AccessDeniedHttpException('Niet gevonden');
		}

		$managerRegistry->getManager()->remove($rememberOAuth);

		$response = $this->tableData([
			new RemoveDataTableEntry($rememberOAuth->id, RememberOAuth::class),
		]);

		$managerRegistry->getManager()->flush();

		return $response;
	}
}
