<?php

use Firebase\JWT\JWT;

require_once 'configuratie.include.php';
require_once 'model/security/RememberLoginModel.class.php';

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
		$timeout = AccountModel::instance()->moetWachten($account);

		if ($timeout === 0) {

			// Check password
			$validPassword = AccountModel::instance()->controleerWachtwoord($account, $pass);

			if ($validPassword) {
				AccountModel::instance()->successfulLoginAttempt($account);
                $_SESSION['_authenticationMethod'] = AuthenticationMethod::cookie_token;
				$credentialsAreValid = true;
			} else {
				AccountModel::instance()->failedLoginAttempt($account);
			}

		}
	}

}

if ($credentialsAreValid) {

	// Generate JWT
	$tokenId = base64_encode(openssl_random_pseudo_bytes(32));
	$issuedAt = time();

	$data = [
		'iat' => $issuedAt,
		'exp' => $issuedAt + JWT_LIFETIME,
		'jti' => $tokenId,
		'data' => [
			'userId' => $account->uid
		]
	];

	// Encode the JWT
	$token = JWT::encode($data, JWT_SECRET, 'HS512');

	// Register uid for this session
	$_SESSION['_uid'] = $account->uid;

	// Generate a refresh token
	$rand = crypto_rand_token(255);

	// Save the refresh token
	$remember = RememberLoginModel::instance()->nieuw();
	$remember->lock_ip = false;
	$remember->device_name = 'API 2.0: ' . filter_var(strval($_SERVER['HTTP_USER_AGENT']), FILTER_SANITIZE_STRING);
	$remember->token = hash('sha512', $rand);
	RememberLoginModel::instance()->create($remember);

	// Respond with both tokens
	$unencodedArray = [
		'token' => $token,
		'refreshToken' => $rand
	];

	header('Content-type: application/json');
	echo json_encode($unencodedArray);
	exit;

} else {

  // Failed login
	http_response_code(401);
	exit;

}
