<?php


namespace CsrDelft\service\security;


use CsrDelft\entity\security\RememberLogin;

interface ILoginService {
	public function login($user, $pass_plain, $evtWachten = true, RememberLogin $remember = null, $lockIP = false, $alreadyAuthenticatedByUrlToken = false, $expire = null);
	public function logout();
	public function getAuthenticationMethod();
	public function authenticate();
}
