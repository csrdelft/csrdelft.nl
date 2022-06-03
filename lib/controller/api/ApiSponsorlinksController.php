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
     * @Route("/API/2.0/sponsorlinks", methods={"GET"})
     * @Auth(P_PUBLIC)
     */
    public function getSponsorlinks()
    {
        $json = file_get_contents(DATA_PATH . 'sponsorlinks.json');
        return new Response($json, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/API/2.0/sponsorlinks/timestamp", methods={"GET"})
     * @Auth(P_PUBLIC)
     */
    public function getTimestamp()
    {
        return new JsonResponse(filemtime(DATA_PATH . 'sponsorlinks.json'));
    }
}
