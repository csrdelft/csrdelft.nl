<?php

use CsrDelft\model\entity\groepen\Activiteit;
use CsrDelft\model\entity\groepen\Bestuur;
use CsrDelft\model\entity\groepen\Commissie;
use CsrDelft\model\entity\groepen\Ketzer;
use CsrDelft\model\entity\groepen\Kring;
use CsrDelft\model\entity\groepen\Lichting;
use CsrDelft\model\entity\groepen\Ondervereniging;
use CsrDelft\model\entity\groepen\RechtenGroep;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\entity\groepen\Werkgroep;
use CsrDelft\model\entity\groepen\Woonoord;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class GroepVue extends AbstractMigration {
	const GROEPEN = [
		Activiteit::class,
		Bestuur::class,
		Commissie::class,
		Ketzer::class,
		Kring::class,
		Lichting::class,
		Ondervereniging::class,
		RechtenGroep::class,
		Verticale::class,
		Werkgroep::class,
		Woonoord::class,
	];

	public function change() {
		$groep_tables = array_map([$this, 'getTableName'], self::GROEPEN);
		$groepleden_tables = array_map([$this, 'getLedenTableName'], self::GROEPEN);

		foreach ($groep_tables as $groep) {
			$this->table($groep)
				->addColumn('versie', 'enum', ['values' => ['v1', 'v2'], 'default' => 'v1'])
				->addColumn('keuzelijst2', 'text', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
				->save();
		}

		foreach ($groepleden_tables as $lid) {
			$this->table($lid)
				->addColumn('opmerking2', 'text', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
				->save();
		}
	}

	protected function getTableName($class) {
		$prop = (new ReflectionObject(new $class()))->getProperty( 'table_name' );
		$prop->setAccessible(true);
		return $prop->getValue();
	}

	protected function getLedenTableName($class) {
		return $this->getTableName(($class::LEDEN)::ORM);
	}
}
