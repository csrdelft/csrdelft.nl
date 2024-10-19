<?php

namespace CsrDelft\controller\api;

use Symfony\Component\Routing\Attribute\Route;
use LogicException;
use CsrDelft\controller\AbstractController;
use CsrDelft\service\security\ApiAuthenticator;

class ApiAuthController extends AbstractController
{
	/**
	 * @see ApiAuthenticator
	 */
	#[Route(path: '/API/2.0/auth/authorize', methods: ['POST'])]
	public function postAuthorize(): never
	{
		throw new LogicException(
			'Deze request wordt opgevangen door ApiAuthenticator.'
		);
	}

	/**
	 * @see ApiAuthenticator
	 */
	#[Route(path: '/API/2.0/auth/token', methods: ['POST'])]
	public function postToken(): never
	{
		throw new LogicException(
			'Deze request wordt opgevangen door ApiAuthenticator.'
		);
	}
}
