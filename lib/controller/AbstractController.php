<?php


namespace CsrDelft\controller;

use CsrDelft\view\datatable\DataTable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;

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
		return $this->container->get('request_stack')
			->getCurrentRequest()
			->request->filter(DataTable::POST_SELECTION, [], FILTER_SANITIZE_STRING);
	}
}
