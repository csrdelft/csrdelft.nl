<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

class EetplanBekendenTable extends DataTable
{
    public function __construct()
    {
        parent::__construct(EetplanBekenden::class, '/eetplan/novietrelatie', 'Novieten die elkaar kennen');
        $this->selectEnabled = false;
        $this->addColumn('noviet2', 'opmerking');
        $this->addColumn('noviet1', 'noviet2');
        $this->searchColumn('noviet1');
        $this->searchColumn('noviet2');
        $this->searchColumn('opmerking');

        $this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl . '/toevoegen', 'Toevoegen', 'Bekenden toevoegen', 'add'));
        $this->addRowKnop(new DataTableRowKnop($this->dataUrl . '/verwijderen', 'Bekenden verwijderen', 'cross', 'confirm'));
        $this->addRowKnop(new DataTableRowKnop($this->dataUrl . '/bewerken', 'Bewerk opmerking', 'pencil'));
    }
}
