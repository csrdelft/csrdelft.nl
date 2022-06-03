<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\entity\maalcie\ArchiefMaaltijd;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;

class ArchiefMaaltijdenTable extends DataTable
{
    public function __construct()
    {
        parent::__construct(ArchiefMaaltijd::class, '/maaltijden/beheer/archief');
        $this->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());
    }
}
