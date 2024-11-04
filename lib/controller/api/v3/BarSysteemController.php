<?php

namespace CsrDelft\controller\api\v3;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\bar\BarLocatie;
use CsrDelft\service\BarSysteemService;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Class BarSysteemController
 * @package CsrDelft\controller\api\v3
 */
#[Route(path: '/api/v3/bar')]
class BarSysteemController extends AbstractController
{


	/**
	 * @param (\CsrDelft\entity\fiscaat\CiviProduct|\CsrDelft\entity\fiscaat\CiviSaldo)[]|BarLocatie $data
	 *
	 * @psalm-param BarLocatie|array<\CsrDelft\entity\fiscaat\CiviProduct|\CsrDelft\entity\fiscaat\CiviSaldo> $data
	 */
	protected function json(
		array|BarLocatie $data,
		int $status = 200,
		array $headers = [],
		array $context = []
	): JsonResponse {
		return parent::json(
			$data,
			$status,
			$headers,
			$context + ['groups' => ['bar']]
		);
	}
}
