<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\bbcode\ProsemirrorToBb;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BbController extends AbstractController
{
	/**
	 * Geef een voorbeeld van een snippet bb code.
	 *
	 * @Route("/bb/preview")
	 * @Auth(P_PUBLIC)
	 * @param Request $request
	 * @return Response
	 */
	public function preview(Request $request)
	{
		$input = json_decode($request->getContent(), true);

		if ($request->request->has('data')) {
			$string = urldecode($request->request->get('data'));
		} elseif ($request->query->has('data')) {
			$string = $request->query->get('data');
		} elseif (isset($input['data'])) {
			$string = urldecode($input['data']);
		} else {
			throw new BadRequestHttpException("Veld data niet gezet");
		}

		$string = trim($string);

		if ($request->request->has('mail') || isset($input['mail'])) {
			return new Response(CsrBB::parseMail($string));
		} else {
			return new Response(CsrBB::parse($string));
		}
	}

	/**
	 * @param Request $request
	 * @param ProsemirrorToBb $prosemirrorToBb
	 * @return Response
	 * @Route("/bb/convert-to-bb")
	 * @Auth(P_PUBLIC)
	 */
	public function convertToBb(Request $request, ProsemirrorToBb $prosemirrorToBb)
	{
		$input = json_decode($request->getContent(), true);

		if (isset($input['data'])) {
			return new Response($prosemirrorToBb->convertToBb($input['data']));
		}

		throw new BadRequestHttpException("Veld data in body niet gezet.");
	}

	/**
	 * @param Request $request
	 * @param BbToProsemirror $bbToProsemirror
	 * @return JsonResponse
	 * @Route("/bb/convert-to-prosemirror")
	 * @Auth(P_PUBLIC)
	 */
	public function convertToProsemirror(Request $request, BbToProsemirror $bbToProsemirror)
	{
		$input = json_decode($request->getContent(), true);

		if (isset($input['data'])) {
			return new JsonResponse($bbToProsemirror->toProseMirror($input['data']));
		}

		throw new BadRequestHttpException("Veld data in body niet gezet.");
	}
}
