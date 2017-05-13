<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class EetplanHuizenTable extends DataTable
{
    public function __construct()
    {
        parent::__construct('EetplanHuizenData', '/eetplan/woonoorden/', 'Woonoorden die meedoen');
        $this->searchColumn('naam');
        $this->addColumn('eetplan', null, null, 'switchButton_' . $this->dataTableId);
        $this->addKnop(new DataTableKnop(">= 1", $this->dataTableId, $this->dataUrl . 'aan', 'post', 'Aanmelden', 'Woonoorden aanmelden voor eetplan', 'add'));
        $this->addKnop(new DataTableKnop(">= 1", $this->dataTableId, $this->dataUrl . 'uit', 'post', 'Afmelden', 'Woonoorden afmelden voor eetplan', 'delete'));
    }

    public function getJavascript()
    {
        return parent::getJavascript() . <<<JS
function switchButton_{$this->dataTableId} (data) {
    return '<span class="ico '+(data?'tick':'cross')+'"></span>';
}
JS;

    }
}
