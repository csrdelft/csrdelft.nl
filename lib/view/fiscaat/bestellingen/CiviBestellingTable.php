<?php

namespace CsrDelft\view\fiscaat\bestellingen;

use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2017
 */
class CiviBestellingTable extends DataTable
{
    public function __construct($uid = null)
    {
        $dataUrl = '/fiscaat/bestellingen' . ($uid == null ? '' : '/' . $uid);
        $titel = $uid == null ? 'Eigen overzicht' : 'Overzicht voor ' . ProfielRepository::getNaam($uid, 'volledig');
        parent::__construct(CiviBestelling::class, $dataUrl, $titel);

        $this->addColumn('inhoud');
        $this->addColumn('totaal', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());
        $this->hideColumn('deleted');
        $this->searchColumn('inhoud');
        $this->searchColumn('moment');

        $this->setOrder(['moment' => 'desc']);
    }

    public function getBreadcrumbs()
    {
        return '<a href="/" title="Startpagina"><span class="fas fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fas fa-eur module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
    }
}
