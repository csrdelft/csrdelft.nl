<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\view\datatable\DataTableResponse;

class BibliotheekCatalogusDatatableResponse extends DataTableResponse
{

    /**
     * @param Boek $entity
     */
    public function renderElement($entity)
    {
        $arr = (array)$entity;
        $arr['titel_link'] = "<a href='{$entity->getUrl()}'>$entity->titel</a>";
        $arr['recensie_count'] = sizeof($entity->getRecensies());
        return $arr;
    }


}
