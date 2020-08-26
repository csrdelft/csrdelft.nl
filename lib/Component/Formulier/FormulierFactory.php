<?php


namespace CsrDelft\Component\Formulier;


use Symfony\Component\DependencyInjection\ContainerInterface;

class FormulierFactory {
	/**
	 * @var ContainerInterface
	 */
	private $registry;
	/**
	 * @var FormulierBuilder
	 */
	private $formulierBuilder;

	public function __construct($registry, FormulierBuilder $formulierBuilder) {
		$this->registry = $registry;
		$this->formulierBuilder = $formulierBuilder;
	}

	public function create(string $type, $data, $options) {
		/** @var FormulierTypeInterface $typeInstance */
		$typeInstance = $this->registry->get($type);

		$typeInstance->createFormulier($this->formulierBuilder, $data, $options);

		$instance = $this->formulierBuilder->getFormulier();
		$instance->setModel($data);

		return $instance;
	}
}
