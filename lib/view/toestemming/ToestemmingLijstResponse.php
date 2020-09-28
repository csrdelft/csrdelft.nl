<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\entity\LidToestemming;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 02/04/2019
 */
class ToestemmingLijstResponse extends DataTableResponse {

    private $categorien;

    public function __construct($model, $categorien) {
			$this->categorien = $categorien;
			parent::__construct($model);
    }

    /**
     * @param LidToestemming[] $entity
     */
    public function renderElement($entity) {
        $profiel = $entity[0]->profiel;

        $arr = [
            'uid' => $profiel->uid,
            'status' => $profiel->status,
            'lid' => $profiel->getLink('volledig'),
        ];


        foreach ($entity as $toestemming) {
            $arr[$toestemming->instelling] = $toestemming->waarde;
        }

        foreach ($this->categorien as $categorie) {
            if (!isset($arr[$categorie])) {
                $arr[$categorie] = 'nee';
            }
        }

        return $arr;

    }
}
