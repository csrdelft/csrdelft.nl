<?php


namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\view\datatable\DataTable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Voor eventuele generieke controller methodes.
 *
 * @package CsrDelft\controller
 */
class AbstractController extends BaseController {
	/**
	 * Haal de DataTable selectie uit POST.
	 *
	 * @return string[]
	 */
	protected function getDataTableSelection() {
		$selection = $this->container->get('request_stack')
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
					throw new CsrToegangException();
				}
			}
			return parent::redirect($url, $status);

	}
}
