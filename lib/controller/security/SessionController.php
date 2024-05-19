<?php

namespace CsrDelft\controller\security;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\security\RememberLogin;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\login\RememberLoginForm;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\RememberMe\PersistentRememberMeHandler;

/**
 * Beheren van sessies en specifiek rememberme sessies.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class SessionController extends AbstractController
{
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;
	/**
	 * @var ObjectManager
	 */
	private $objectManager;

	public function __construct(
		ManagerRegistry $managerRegistry,
		RememberLoginRepository $rememberLoginRepository
	) {
		$this->rememberLoginRepository = $rememberLoginRepository;
		$this->objectManager = $managerRegistry->getManager();
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/rememberdata", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function rememberdata(): GenericDataTableResponse
	{
		return $this->tableData(
			$this->rememberLoginRepository->findBy(['uid' => $this->getUid()])
		);
	}

	/**
	 * @param Request $request
	 * @param PersistentRememberMeHandler $rememberMeHandler
	 * @return RememberLoginForm|Response
	 * @Route("/session/remember", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remember(
		Request $request,
		PersistentRememberMeHandler $rememberMeHandler
	) {
		$selection = $this->getDataTableSelection();

		if (empty($selection)) {
			$response = new Response();

			$request->request->set('_remember_me', true);
			$rememberMeHandler->createRememberMeCookie($this->getUser());

			return $response;
		}

		$remember = $this->rememberLoginRepository->retrieveByUUID($selection[0]);

		if (!$remember || $remember->uid !== $this->getUid()) {
			throw $this->createAccessDeniedException();
		}
		$form = new RememberLoginForm($remember);
		if ($form->validate()) {
			if (isset($_POST['DataTableId'])) {
				$response = $this->tableData([$remember]);
			} elseif (!empty($_POST['redirect'])) {
				$response = new JsonResponse($_POST['redirect']);
			} else {
				$response = new JsonResponse($this->generateUrl('default'));
			}

			$this->objectManager->persist($remember);
			$this->objectManager->flush();

			return $response;
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/forget-all", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function forgetAll(): GenericDataTableResponse
	{
		$remembers = $this->rememberLoginRepository->findBy([
			'uid' => $this->getUid(),
		]);

		$response = [];
		foreach ($remembers as $remember) {
			$response[] = new RemoveDataTableEntry(
				$remember->id,
				RememberLogin::class
			);
			$this->objectManager->remove($remember);
		}
		$this->objectManager->flush();

		return $this->tableData($response);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/forget", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function forget(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();
		if (!$selection) {
			throw $this->createAccessDeniedException();
		}
		$response = [];
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginRepository->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== $this->getUid()) {
				throw $this->createAccessDeniedException();
			}
			$response[] = new RemoveDataTableEntry(
				$remember->id,
				RememberLogin::class
			);
			$this->objectManager->remove($remember);
		}
		$this->objectManager->flush();
		return $this->tableData($response);
	}
}
