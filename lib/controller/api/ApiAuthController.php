<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\security\AccountModel;
use CsrDelft\repository\security\RememberLoginRepository;
use Exception;
use Firebase\JWT\JWT;
use Jacwright\RestServer\RestException;

class ApiAuthController {
	private $accountModel;
	private $rememberLoginRepository;

	public function __construct() {
		$container = ContainerFacade::getContainer();

		$this->rememberLoginRepository = $container->get(RememberLoginRepository::class);
		$this->accountModel = $container->get(AccountModel::class);
	}

	/**
	 * @return boolean
	 */
	public static function isAuthorized() {
		if (!isset($_SERVER['HTTP_X_CSR_AUTHORIZATION'])) {
			throw new RestException(400);
		}

		$authHeader = $_SERVER['HTTP_X_CSR_AUTHORIZATION'];

		$jwt = substr($authHeader, 7);

		if (!$jwt) {
			throw new RestException(400);
		}

		try {
			$token = JWT::decode($jwt, env('JWT_SECRET'), array('HS512'));
		} catch (Exception $e) {
			throw new RestException(401);
		}

		$_SESSION['_uid'] = $token->data->userId;
		$_SESSION['_authenticationMethod'] = AuthenticationMethod::cookie_token;

		return true;
	}

	/**
	 * @noAuth
	 * @url POST /authorize
	 */
	public function postAuthorize() {
		$credentialsAreValid = false;
		$account = null;

		// Check credentials
		if (isset($_POST['user']) && isset($_POST['pass'])) {

			// Filter posted data
			$user = filter_var(strval($_POST['user']), FILTER_SANITIZE_STRING);
			$pass = filter_var(strval($_POST['pass']), FILTER_SANITIZE_STRING);

			// Check uid
			if (AccountModel::isValidUid($user)) {
				$account = AccountModel::get($user);
			}

			// Check account
			if ($account) {

				// Check timeout
				$timeout = $this->accountModel->moetWachten($account);

				if ($timeout === 0) {

					// Check password
					$validPassword = $this->accountModel->controleerWachtwoord($account, $pass);

					if ($validPassword) {
						$this->accountModel->successfulLoginAttempt($account);
						$_SESSION['_authenticationMethod'] = AuthenticationMethod::cookie_token;
						$credentialsAreValid = true;
					} else {
						$this->accountModel->failedLoginAttempt($account);
					}

				}
			}

		}

		if (!$credentialsAreValid) {
			throw new RestException(401);
		}

		// Generate JWT
		$tokenId = base64_encode(openssl_random_pseudo_bytes(32));
		$issuedAt = time();

		$data = [
			'iat' => $issuedAt,
			'exp' => $issuedAt + env('JWT_LIFETIME'),
			'jti' => $tokenId,
			'data' => [
				'userId' => $account->uid
			]
		];

		// Encode the JWT
		$token = JWT::encode($data, env('JWT_SECRET'), 'HS512');

		// Register uid for this session
		$_SESSION['_uid'] = $account->uid;

		// Generate a refresh token
		$rand = crypto_rand_token(255);

		// Save the refresh token
		$remember = $this->rememberLoginRepository->nieuw();
		$remember->lock_ip = false;
		$remember->device_name = 'API 2.0: ' . filter_var(strval($_SERVER['HTTP_USER_AGENT']), FILTER_SANITIZE_STRING);
		$remember->token = hash('sha512', $rand);
		$this->rememberLoginRepository->create($remember);

		// Respond with both tokens
		return [
			'token' => $token,
			'refreshToken' => $rand
		];
	}

	/**
	 * @url POST /token
	 */
	public function postToken() {

		// Check for token
		if (!isset($_POST['refresh_token'])) {
			throw new RestException(401);
		}

		// Filter posted data
		$refresh_token = filter_var(strval($_POST['refresh_token']), FILTER_SANITIZE_STRING);

		// Check refresh token
		$remember = $this->rememberLoginRepository->find('token = ?', array(hash('sha512', $refresh_token)), null, null, 1)->fetch();

		if (!$remember) {
			throw new RestException(401);
		}

		// Generate new JWT
		$tokenId = base64_encode(openssl_random_pseudo_bytes(32));
		$issuedAt = time();

		$data = [
			'iat' => $issuedAt,
			'exp' => $issuedAt + env('JWT_LIFETIME'),
			'jti' => $tokenId,
			'data' => [
				'userId' => $remember->uid
			]
		];

		// Encode the new JWT
		$token = JWT::encode($data, env('JWT_SECRET'), 'HS512');

		// Respond
		return [
			'token' => $token
		];
	}

}
