<?php


namespace CsrDelft\controller;

use CsrDelft\Component\Formulier\FormulierFactory;
use CsrDelft\Component\Formulier\FormulierInstance;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\Component\DataTable\DataTableFactory;
use CsrDelft\Component\DataTable\DataTableInstance;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\GenericDataTableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

/**
 * Voor eventuele generieke controller methodes.
 *
 * @package CsrDelft\controller
 * @method Account|null getUser()
 */
class AbstractController extends BaseController
{
	public static function getSubscribedServices()
	{
		return parent::getSubscribedServices() + [
				'csr.table.factory' => DataTableFactory::class,
				'csr.formulier.factory' => FormulierFactory::class,
			];
	}

	/**
	 * Haal de DataTable selectie uit POST.
	 *
	 * @return string[]
	 */
	protected function getDataTableSelection(): array
	{
		$selection = $this->get('request_stack')
			->getCurrentRequest()
			->request->filter(DataTable::POST_SELECTION, [], FILTER_SANITIZE_STRING);

		if (is_string($selection) && !empty($selection)) {
			return [$selection];
		}

		return $selection;
	}

	protected function tableData($data, $groups = null): GenericDataTableResponse
	{
		return new GenericDataTableResponse($this->get('serializer'), $data, null, null, $groups);
	}

	/**
	 * @return string|null
	 */
	protected function getUid(): ?string
	{
		$user = $this->getUser();
		if ($user) {
			return $user->uid;
		}
		return null;
	}

	/**
	 * @return Profiel|null
	 */
	protected function getProfiel(): ?Profiel
	{
		$user = $this->getUser();
		if ($user) {
			return $user->profiel;
		}
		return null;
	}

	protected function createAccessDeniedException(string $message = 'Geen Toegang.', Throwable $previous = null): AccessDeniedException
	{
		return parent::createAccessDeniedException($message, $previous);
	}

	protected function createNotFoundException(string $message = 'Niet gevonden', Throwable $previous = null): NotFoundHttpException
	{
		return parent::createNotFoundException($message, $previous);
	}

	/**
	 * Creates and returns a Form instance from the type of the form.
	 * @param string $type
	 * @param null $data
	 * @param array $options
	 * @return FormulierInstance
	 */
	protected function createFormulier(string $type, $data = null, array $options = []): FormulierInstance
	{
		return $this->container->get('csr.formulier.factory')->create($type, $data, $options);
	}

	/**
	 * @param $type
	 * @param array $options
	 * @return DataTableInstance
	 */
	protected function createDataTable($type, $options = [])
	{
		return $this->container->get('csr.table.factory')->create($type, $options)->getTable();
	}
}
