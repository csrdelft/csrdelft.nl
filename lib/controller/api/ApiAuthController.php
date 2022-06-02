<?php

namespace CsrDelft\controller\api;

use CsrDelft\controller\AbstractController;
use CsrDelft\service\security\ApiAuthenticator;
use Symfony\Component\Routing\Annotation\Route;

class ApiAuthController extends AbstractController
{
	/**
	 * @Route("/API/2.0/auth/authorize", methods={"POST"})
	 * @see ApiAuthenticator
	 */
	public function postAuthorize()
	{
		throw new \LogicException("Deze request wordt opgevangen door ApiAuthenticator.");
	}

	/**
	 * @Route("/API/2.0/auth/token", methods={"POST"})
	 * @see ApiAuthenticator
	 */
	public function postToken()
	{
		throw new \LogicException("Deze request wordt opgevangen door ApiAuthenticator.");
	}
}
