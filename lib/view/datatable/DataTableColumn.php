<?php

namespace CsrDelft\view\datatable;

use JsonSerializable;

/**
 * Een kolom in een datatable met optionele extra render waarde.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/11/2018
 */
class DataTableColumn implements JsonSerializable
{
    /**
     * Waarde die wordt weergegeven, kan HTML bevatten.
     * @var string
     */
    public $displayValue;
    /**
     * Waarde waarop gesorteerd wordt.
     * @var string
     */
    public $sortValue;
    /**
     * Waarde die wordt weergegeven als wordt geëxporteerd naar excel/pdf/print.
     * @var string
     */
    public $exportValue;
    /**
     * Waarde waarop gefilterd wordt.
     * @var string
     */
    public $filterValue;

    public function __construct($displayValue, $sortValue = null, $exportValue = null, $filterValue = null)
    {
        $this->displayValue = $displayValue;
        $this->sortValue = $sortValue ?? $this->displayValue;
        $this->exportValue = $exportValue ?? $this->sortValue;
        $this->filterValue = $filterValue ?? $this->exportValue;
    }

    public function jsonSerialize()
    {
        return [
            'display' => $this->displayValue,
            'sort' => $this->sortValue,
            'export' => $this->exportValue,
            'filter' => $this->filterValue,
        ];
    }
}
