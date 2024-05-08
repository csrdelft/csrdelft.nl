<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\entity\security\AccessControl;
use CsrDelft\repository\security\AccessRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\RechtenForm;
use CsrDelft\view\RechtenTable;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RechtenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de ACL.
 */
class RechtenController extends AbstractController
{
	/**
	 * @var AccessRepository
	 */
	private $accessRepository;

	public function __construct(AccessRepository $accessRepository)
	{
		$this->accessRepository = $accessRepository;
	}

	/**
	 * @param null $environment
	 * @param null $resource
	 * @return Response
	 * @Route("/rechten/bekijken/{environment}/{resource}", methods={"GET"}, defaults={"environment"=null,"resource"=null})
	 * @Auth(P_LOGGED_IN)
	 */
	public function bekijken($environment = null, $resource = null): Response
	{
		return $this->render('default.html.twig', [
			'content' => new RechtenTable(
				$this->accessRepository,
				$environment,
				$resource
			),
		]);
	}

	/**
	 * @param null $environment
	 * @param null $resource
	 * @return GenericDataTableResponse
	 * @Route("/rechten/bekijken/{environment}/{resource}", methods={"POST"}, defaults={"environment"=null,"resource"=null})
	 * @Auth(P_LOGGED_IN)
	 */
	public function data($environment = null, $resource = null): GenericDataTableResponse
	{
		return $this->tableData(
			$this->accessRepository->getTree($environment, $resource)
		);
	}

	/**
	 * @param null $environment
	 * @param null $resource
	 * @return GenericDataTableResponse|RechtenForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/rechten/aanmaken/{environment}/{resource}", methods={"POST"}, defaults={"environment"=null,"resource"=null})
	 * @Auth(P_LOGGED_IN)
	 */
	public function aanmaken($environment = null, $resource = null): GenericDataTableResponse|RechtenForm
	{
		$ac = $this->accessRepository->nieuw($environment, $resource);
		$form = new RechtenForm($ac, 'aanmaken');
		if ($form->validate()) {
			$this->accessRepository->setAcl($ac->environment, $ac->resource, [
				$ac->action => $ac->subject,
			]);
			return $this->tableData([$ac]);
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse|RechtenForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/rechten/wijzigen", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function wijzigen(): GenericDataTableResponse|RechtenForm
	{
		$selection = $this->getDataTableSelection();

		if (!isset($selection[0])) {
			throw $this->createAccessDeniedException();
		}

		/** @var AccessControl $ac */
		$ac = $this->accessRepository->retrieveByUUID($selection[0]);
		$form = new RechtenForm($ac, 'wijzigen');

		if ($form->validate()) {
			$this->accessRepository->setAcl($ac->environment, $ac->resource, [
				$ac->action => $ac->subject,
			]);
			return $this->tableData([$ac]);
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/rechten/verwijderen", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		$response = [];

		foreach ($selection as $UUID) {
			/** @var AccessControl $ac */
			$ac = $this->accessRepository->retrieveByUUID($UUID);
			$response[] = new RemoveDataTableEntry(
				explode('@', $UUID)[0],
				AccessControl::class
			);
			$this->accessRepository->setAcl($ac->environment, $ac->resource, [
				$ac->action => null,
			]);
		}

		return $this->tableData($response);
	}
}
