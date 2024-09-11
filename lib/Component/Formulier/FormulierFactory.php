<?php

namespace CsrDelft\Component\Formulier;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FormulierFactory
{
	/**
	 * @param ContainerInterface $registry
	 */
	public function __construct(
		private $registry,
		private readonly RequestStack $requestStack,
		private readonly FormulierBuilder $formulierBuilder
	) {
	}

	public function create(string $type, $data, $options)
	{
		/** @var FormulierTypeInterface $typeInstance */
		$typeInstance = $this->registry->get($type);

		if (isset($options['action'])) {
			$this->formulierBuilder->setAction($options['action']);
		} else {
			$this->formulierBuilder->setAction(
				$this->requestStack->getCurrentRequest()->getRequestUri()
			);
		}

		if (isset($options['dataTableId'])) {
			$this->formulierBuilder->setDataTableId($options['dataTableId']);
		}

		$typeInstance->createFormulier($this->formulierBuilder, $data, $options);

		$instance = $this->formulierBuilder->getFormulier();
		$instance->setModel($data);

		return $instance;
	}
}
