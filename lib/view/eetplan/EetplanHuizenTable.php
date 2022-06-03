<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\Component\DataTable\AbstractDataTableType;
use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EetplanHuizenTable extends AbstractDataTableType {
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void
	{
		$builder->loadFromClass(EetplanHuizenData::class);
		$builder->setDataUrl($this->urlGenerator->generate('csrdelft_eetplan_woonoorden'));
		$builder->setTitel('Woonoorden die meedoen');
		$builder->resetButtons();
		$builder->selectEnabled = false;
		$builder->searchColumn('naam');
		$builder->addColumn('eetplan', null, null, CellRender::Check());
		$builder->addRowKnop(new DataTableRowKnop($this->urlGenerator->generate('csrdelft_eetplan_woonoorden_toggle'), 'Woonoord aan/af melden voor eetplan', 'arrow_refresh'));
	}
}
