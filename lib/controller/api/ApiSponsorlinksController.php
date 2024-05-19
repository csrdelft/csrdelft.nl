<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @since 04/11/2017
 */
class ApiSponsorlinksController
{
	/**
  * @Auth(P_PUBLIC)
  */
 #[Route(path: '/API/2.0/sponsorlinks', methods: ['GET'])]
 public function getSponsorlinks(): Response
	{
		$json = file_get_contents(DATA_PATH . 'sponsorlinks.json');
		return new Response($json, 200, ['Content-Type' => 'application/json']);
	}

	/**
  * @Auth(P_PUBLIC)
  */
 #[Route(path: '/API/2.0/sponsorlinks/timestamp', methods: ['GET'])]
 public function getTimestamp(): JsonResponse
	{
		return new JsonResponse(filemtime(DATA_PATH . 'sponsorlinks.json'));
	}
}
