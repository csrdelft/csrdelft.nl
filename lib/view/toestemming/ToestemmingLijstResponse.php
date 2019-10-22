<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\model\entity\LidToestemming;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 02/04/2019
 */
class ToestemmingLijstResponse extends DataTableResponse {

    private $categorien;

    public function __construct($model, $categorien) {
        parent::__construct($model);
        $this->categorien = $categorien;
    }

    /**
     * @param LidToestemming[] $entity
     */
    public function renderElement($entity) {
        $profiel = ProfielModel::get($entity[0]->uid);

        $arr = [
            'uid' => $entity[0]->uid,
            'status' => $profiel->status,
            'lid' => $profiel->getLink(),
        ];


        foreach ($entity as $toestemming) {
            $arr[$toestemming->instelling_id] = $toestemming->waarde;
        }

        foreach ($this->categorien as $categorie) {
            if (!isset($arr[$categorie])) {
                $arr[$categorie] = 'nee';
            }
        }

        return $arr;

    }
}
