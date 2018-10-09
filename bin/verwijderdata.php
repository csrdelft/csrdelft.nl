<?php
use CsrDelft\model\ProfielModel;

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

foreach (ProfielModel::instance()->find() as $profiel) {
    if(ProfielModel::instance()->verwijderVeldenUpdate($profiel)) {
        echo "Verwijder data van " . $profiel->uid . "\n";
	}
}
