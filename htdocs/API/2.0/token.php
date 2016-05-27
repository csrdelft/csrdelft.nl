<?php

require_once 'configuratie.include.php';
require_once 'php-jwt/JWT.php';
require_once 'model/security/RememberLoginModel.class.php';

// Check for token
if (isset($_POST['refresh_token'])) {

	// Filter posted data
	$refresh_token = filter_var(strval($_POST['refresh_token']), FILTER_SANITIZE_STRING);

	// Check refresh token
	$remember = RememberLoginModel::instance()->find('token = ?', array(hash('sha512', $refresh_token)), null, null, 1)->fetch();
	if ($remember) {

		// Generate new JWT
		$tokenId = base64_encode(openssl_random_pseudo_bytes(32));
		$issuedAt = time();

		$data = [
			'iat' => $issuedAt,
			'exp' => $issuedAt + JWT_LIFETIME,
			'jti' => $tokenId,
			'data' => [
				'userId' => $remember->uid
			]
		];

		// Encode the new JWT
		$token = JWT::encode($data, JWT_SECRET, 'HS512');

		// Respond
		$unencodedArray = [
			'token' => $token
		];

		header('Content-type: application/json');
		echo json_encode($unencodedArray);
		exit;

	} else {

		// Invalid refresh token
		http_response_code(401);
		exit;

	}

} else {

	// No refresh token posted
	http_response_code(401);
	exit;

}
