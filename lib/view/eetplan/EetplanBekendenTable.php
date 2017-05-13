<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\EetplanBekendenModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class EetplanBekendenTable extends DataTable
{
    public function __construct()
    {
        parent::__construct(EetplanBekendenModel::ORM, '/eetplan/novietrelatie/', 'Novieten die elkaar kennen');
        $this->addColumn('noviet1');
        $this->addColumn('noviet2');
        $this->searchColumn('noviet1');
        $this->searchColumn('noviet2');

        $this->addKnop(new DataTableKnop("== 0", $this->dataTableId, $this->dataUrl . 'toevoegen', 'post popup', 'Toevoegen', 'Bekenden toevoegen', 'add'));
        $this->addKnop(new DataTableKnop(">= 1", $this->dataTableId, $this->dataUrl . 'verwijderen', 'post confirm', 'Verwijderen', 'Bekenden verwijderen', 'cross'));
    }
}
