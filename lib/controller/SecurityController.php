<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {
	/**
	 * @Route("/login", name="app_login")
	 * @Auth(P_PUBLIC)
	 */
	public function login(AuthenticationUtils $authenticationUtils): Response {
		if ($this->getUser()) {
			return $this->redirectToRoute('default');
		}

		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		// TODO doe hier iets mee

		return $this->redirectToRoute('default');
	}

	/**
	 * @Route("/logout", name="app_logout")
	 * @Auth(P_PUBLIC)
	 */
	public function logout() {
		throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}
}
