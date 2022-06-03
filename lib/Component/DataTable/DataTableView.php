<?php

namespace CsrDelft\Component\DataTable;

class DataTableView
{
    /**
     * @var string
     */
    private $data;

    public function __toString()
    {
        return $this->data;
    }

    public function __construct($data)
    {
        $this->data = $data;
    }
}
