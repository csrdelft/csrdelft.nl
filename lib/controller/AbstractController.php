<?php


namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\GenericDataTableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Voor eventuele generieke controller methodes.
 *
 * @package CsrDelft\controller
 * @method Account|null getUser()
 */
class AbstractController extends BaseController {
	/**
	 * Haal de DataTable selectie uit POST.
	 *
	 * @return string[]
	 */
	protected function getDataTableSelection() {
		$selection = $this->get('request_stack')
			->getCurrentRequest()
			->request->filter(DataTable::POST_SELECTION, [], FILTER_SANITIZE_STRING);

		if (is_string($selection) && !empty($selection)) {
			return [$selection];
		}

		return $selection;
	}

	/**
	 * Redirect only to external urls if explicitly allowed
	 * @param string $url
	 * @param int $status
	 * @param bool $allowExternal
	 * @return RedirectResponse
	 */
	protected function csrRedirect($url, $status = 302, $allowExternal = false)
	{
			if (empty($url) || $url === null) {
				$url = REQUEST_URI;
			}
			if (!startsWith($url, CSR_ROOT) && !$allowExternal) {
				if (preg_match("/^[?#\/]/", $url) === 1) {
					$url = CSR_ROOT . $url;
				} else {
					throw $this->createAccessDeniedException();
				}
			}
			return parent::redirect($url, $status);

	}

	protected function tableData($data) {
		return new GenericDataTableResponse($this->get('serializer'), $data);
	}

	/**
	 * @return string|null
	 */
	protected function getUid() {
		$user = $this->getUser();
		if ($user) {
			return $user->uid;
		}
		return null;
	}

	/**
	 * @return Profiel|null
	 */
	protected function getProfiel() {
		$user = $this->getUser();
		if ($user) {
			return $user->profiel;
		}
		return null;
	}

	protected function createAccessDeniedException(string $message = 'Geen Toegang.', \Throwable $previous = null): AccessDeniedException {
		return parent::createAccessDeniedException($message, $previous);
	}

	protected function createNotFoundException(string $message = 'Niet gevonden', \Throwable $previous = null): NotFoundHttpException {
		return parent::createNotFoundException($message, $previous);
	}
}
