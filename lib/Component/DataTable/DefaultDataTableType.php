<?php


namespace CsrDelft\Component\DataTable;


use Doctrine\ORM\EntityManagerInterface;

class DefaultDataTableType extends AbstractDataTableType {
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void {
		$entityType = $options[AbstractDataTableType::OPTION_ENTITY_TYPE];
		$dataUrl = $options[AbstractDataTableType::OPTION_DATA_URL];
		$titel = $options['titel'];

		$builder->setTitel($titel);
		$builder->setDataUrl($dataUrl);
		$builder->setTableId(uniqid_safe(classNameZonderNamespace($entityType)));
		$builder->addDefaultDetailsColumn();

		$builder->loadFromMetadata($this->entityManager->getClassMetadata($entityType));
	}
}
