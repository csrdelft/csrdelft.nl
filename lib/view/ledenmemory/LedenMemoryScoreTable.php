<?php

namespace CsrDelft\view\ledenmemory;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\repository\LedenMemoryScoresRepository;
use CsrDelft\view\datatable\DataTable;

class LedenMemoryScoreTable extends DataTable {

	public function __construct(
		AbstractGroep $groep = null,
		$titel = null
	) {
		parent::__construct(LedenMemoryScoresRepository::ORM, '/leden/memoryscores' . ($groep ? '/' . $groep->getUUID() : null), 'Topscores Ledenmemory' . $titel, 'groep');
		$this->settings['tableTools']['aButtons'] = array();
		$this->settings['dom'] = 'rtpli';

		$this->hideColumn('goed');
		$this->hideColumn('eerlijk');
		$this->hideColumn('wanneer');

		$this->setColumnTitle('door_uid', 'Naam');
	}

}
