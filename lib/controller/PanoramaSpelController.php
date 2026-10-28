<?php

declare(strict_types=1);

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\service\panorama\PanoramaArcadeService;
use CsrDelft\service\panorama\PanoramaDailyQuiz;
use CsrDelft\service\panorama\PanoramaGameSettings;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Doctrine\DBAL\Exception\TableNotFoundException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanoramaSpelController extends AbstractController
{
	/** @Auth(P_LOGGED_IN) */
	#[Route(path: '/panorama/expeditie', methods: ['GET'])]
	public function oudeSpelingang(): Response
	{
		return $this->redirectToRoute('panorama_spellen');
	}

	/** @Auth(P_LOGGED_IN) */
	#[
		Route(path: '/panorama/spellen', name: 'panorama_spellen', methods: ['GET'])
	]
	public function spel(): Response
	{
		$response = $this->render('lustrum13/spellen.html.twig');
		$response->headers->set('Cache-Control', 'private, no-store');
		$response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
		return $response;
	}

	/** @Auth(P_LOGGED_IN) */
	#[
		Route(
			path: '/panorama/spellen/stand',
			name: 'panorama_spellen_stand',
			methods: ['GET']
		)
	]
	public function stand(
		PanoramaArcadeService $service,
		PanoramaDailyQuiz $quiz
	): JsonResponse {
		try {
			$state = $service->snapshot($this->getUid());
			$state['daily'] = $quiz->snapshot($this->getUid());
			$state['canManage'] = $this->mag('commissie:LustrumCie');
			return $this->privateJson($state);
		} catch (TableNotFoundException $e) {
			return $this->privateJson(
				[
					'error' =>
						'De spelopslag is nog niet geïnstalleerd. Vraag de beheerder om de Panorama-migratie uit te voeren.',
				],
				503
			);
		}
	}

	/** @Auth(P_LOGGED_IN) */
	#[
		Route(
			path: '/panorama/spellen/actie',
			name: 'panorama_spellen_actie',
			methods: ['POST']
		)
	]
	public function actie(
		Request $request,
		PanoramaArcadeService $service
	): JsonResponse {
		if (strlen($request->getContent()) > 8192) {
			return $this->privateJson(
				['error' => 'Deze spelactie is te groot.'],
				413
			);
		}
		$input = $request->request->all();
		$action = $input['action'] ?? '';
		if (!is_string($action)) {
			return $this->privateJson(['error' => 'Ongeldige spelactie.'], 422);
		}
		foreach (['game', 'attempt', 'move', 'answer'] as $field) {
			if (isset($input[$field]) && !is_string($input[$field])) {
				return $this->privateJson(['error' => 'Ongeldige spelgegevens.'], 422);
			}
		}
		try {
			return $this->privateJson(
				$service->act(
					$this->getUid(),
					$this->getProfiel()->getNaam('volledig'),
					$action,
					$input
				)
			);
		} catch (DomainException $e) {
			return $this->privateJson(
				[
					'error' => $e->getMessage(),
					'game' => $service->snapshot($this->getUid()),
				],
				409
			);
		} catch (TableNotFoundException $e) {
			return $this->privateJson(
				['error' => 'De spelopslag is nog niet geïnstalleerd.'],
				503
			);
		}
	}

	/** @Auth(P_LOGGED_IN) */
	#[Route(path: '/panorama/spellen/dagantwoord', methods: ['POST'])]
	public function dagantwoord(
		Request $request,
		PanoramaDailyQuiz $quiz
	): JsonResponse {
		if (strlen($request->getContent()) > 2048) {
			return $this->privateJson(['error' => 'Antwoord te lang.'], 413);
		}
		try {
			return $this->privateJson(
				$quiz->guess(
					$this->getUid(),
					$this->getProfiel()->getNaam('volledig'),
					$request->request->all()
				)
			);
		} catch (DomainException $e) {
			return $this->privateJson(
				[
					'error' => $e->getMessage(),
					'daily' => $quiz->snapshot($this->getUid()),
				],
				409
			);
		}
	}
	/** @Auth("commissie:LustrumCie") */
	#[Route(path: '/panorama/spellen/beheer', methods: ['GET'])]
	public function beheer(): Response
	{
		$response = $this->render('lustrum13/quiz-beheer.html.twig');
		$response->headers->set('Cache-Control', 'private, no-store');
		$response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
		return $response;
	}
	/** @Auth("commissie:LustrumCie") */
	#[Route(path: '/panorama/spellen/beheer/data', methods: ['GET'])]
	public function beheervragen(PanoramaDailyQuiz $quiz): JsonResponse
	{
		return $this->privateJson([
			'today' => $quiz->today(),
			'questions' => $quiz->questions(),
		]);
	}
	/** @Auth("commissie:LustrumCie") */
	#[Route(path: '/panorama/spellen/beheer/opslaan', methods: ['POST'])]
	public function beheersave(
		Request $request,
		PanoramaDailyQuiz $quiz
	): JsonResponse {
		$input = $request->request->all();
		foreach (['day', 'prompt', 'answers', 'credit'] as $field) {
			if (isset($input[$field]) && !is_string($input[$field])) {
				return $this->privateJson(['error' => 'Ongeldige invoer.'], 422);
			}
		}
		$file = $request->files->get('photo');
		if (
			$file !== null &&
			!$file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile
		) {
			return $this->privateJson(['error' => 'Ongeldige afbeelding.'], 422);
		}
		try {
			$id = $quiz->save($this->getUid(), $input, $file);
			return $this->privateJson([
				'id' => $id,
				'questions' => $quiz->questions(),
				'today' => $quiz->today(),
			]);
		} catch (DomainException $e) {
			return $this->privateJson(['error' => $e->getMessage()], 409);
		}
	}
	/** @Auth(P_LOGGED_IN) */
	#[
		Route(
			path: '/panorama/spellen/dagfoto/{id}',
			requirements: ['id' => '\\d+'],
			methods: ['GET']
		)
	]
	public function dagfoto(int $id, PanoramaDailyQuiz $quiz): Response
	{
		$path = $quiz->photo($id, $this->mag('commissie:LustrumCie'));
		if (!$path) {
			throw $this->createNotFoundException();
		}
		$response = new BinaryFileResponse($path);
		$response->headers->set('Content-Type', 'image/webp');
		$response->headers->set('Cache-Control', 'private, no-store');
		$response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
		$response->headers->set('X-Content-Type-Options', 'nosniff');
		return $response;
	}
	/** @Auth("commissie:LustrumCie") */
	#[Route(path: '/panorama/spellen/spelbeheer', methods: ['GET'])]
	public function spelbeheer(): Response
	{
		$response = $this->render('lustrum13/spel-beheer.html.twig');
		$response->headers->set('Cache-Control', 'private, no-store');
		$response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
		return $response;
	}
	/** @Auth("commissie:LustrumCie") */
	#[Route(path: '/panorama/spellen/spelbeheer/data', methods: ['GET'])]
	public function spelbeheerdata(PanoramaGameSettings $settings): JsonResponse
	{
		return $this->privateJson($settings->get());
	}
	/** @Auth("commissie:LustrumCie") */
	#[Route(path: '/panorama/spellen/spelbeheer/opslaan', methods: ['POST'])]
	public function spelbeheersave(
		Request $request,
		PanoramaGameSettings $settings
	): JsonResponse {
		$input = $request->request->all();
		foreach (['revision', 'game', 'name', 'enabled', 'credit'] as $field) {
			if (isset($input[$field]) && !is_string($input[$field])) {
				return $this->privateJson(['error' => 'Ongeldige invoer.'], 422);
			}
		}
		$file = $request->files->get('photo');
		if (
			$file !== null &&
			!$file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile
		) {
			return $this->privateJson(['error' => 'Ongeldige foto.'], 422);
		}
		try {
			return $this->privateJson(
				$settings->save($this->getUid(), $input, $file)
			);
		} catch (DomainException $e) {
			return $this->privateJson(['error' => $e->getMessage()], 409);
		}
	}
	/** @Auth(P_LOGGED_IN) */
	#[
		Route(
			path: '/panorama/spellen/spelfoto/{id}',
			requirements: ['id' => '[a-f0-9]{40}'],
			methods: ['GET']
		)
	]
	public function spelfoto(string $id, PanoramaGameSettings $settings): Response
	{
		$path = $settings->photo($id);
		if (!$path) {
			throw $this->createNotFoundException();
		}
		$response = new BinaryFileResponse($path);
		$response->headers->set('Content-Type', 'image/webp');
		$response->headers->set('Cache-Control', 'private, no-store');
		$response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
		$response->headers->set('X-Content-Type-Options', 'nosniff');
		return $response;
	}
	private function privateJson(array $data, int $status = 200): JsonResponse
	{
		$response = $this->json($data, $status);
		$response->headers->set('Cache-Control', 'private, no-store');
		$response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
		return $response;
	}
}
