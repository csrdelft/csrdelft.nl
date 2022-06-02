<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\Component\DataTable\AbstractDataTableType;
use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/04/2017
 */
class CiviSaldoTable extends AbstractDataTableType
{
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

	public function getBreadcrumbs()
	{
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Saldo</span>';
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void
	{
		$builder->loadFromClass(CiviSaldo::class);
		$builder->setDataUrl($this->urlGenerator->generate('csrdelft_fiscaat_beheercivisaldo_overzicht'));
		$builder->setTitel('Saldobeheer');

		$builder->addColumn('uid', 'saldo');
		$builder->addColumn('naam', 'saldo');
		$builder->addColumn('lichting', 'saldo');
		$builder->addColumn('hidden_saldo', null, null, null, null, null, 'saldo');
		$builder->hideColumn('hidden_saldo');
		$builder->addColumn('saldo', null, null, CellRender::Bedrag(), 'hidden_saldo', CellType::FormattedNumber());
		$builder->setOrder(array('hidden_saldo' => 'asc'));

		$builder->searchColumn('naam');

		$builder->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->urlGenerator->generate('csrdelft_fiscaat_beheercivisaldo_registreren'), 'Registreren', 'Lid registreren', 'toevoegen'));
		$builder->addRowKnop(new DataTableRowKnop($this->urlGenerator->generate('csrdelft_fiscaat_beheercivisaldo_inleggen'), 'Saldo van lid ophogen', 'coins_add'));
		$builder->addRowKnop(new DataTableRowKnop($this->urlGenerator->generate('csrdelft_fiscaat_beheercivisaldo_verwijderen'), 'Saldo van lid verwijderen', 'bin', 'confirm'));
	}
}


