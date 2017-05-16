<?php

namespace CsrDelft\view\ledenmemory;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\view\formulier\datatable\DataTable;

class LedenMemoryScoreTable extends DataTable
{

    public function __construct(
        AbstractGroep $groep = null,
        $titel
    ) {
        parent::__construct(LedenMemoryScoresModel::ORM, '/leden/memoryscores/' . ($groep ? $groep->getUUID() : null), 'Topscores Ledenmemory' . $titel, 'groep');
        $this->settings['tableTools']['aButtons'] = array();
        $this->settings['dom'] = 'rtpli';

        $this->hideColumn('goed');
        $this->hideColumn('eerlijk');
        $this->hideColumn('wanneer');

        $this->setColumnTitle('door_uid', 'Naam');
    }

}
