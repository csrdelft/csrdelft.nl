<?php

use CsrDelft\repository\CourantBerichtRepository;
use CsrDelft\repository\CourantRepository;
use CsrDelft\view\courant\CourantView;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CourantCompile extends AbstractMigration {
	public function up() {
		$this->table('courant')
			->addColumn('inhoud', 'text', ['limit' => MysqlAdapter::TEXT_MEDIUM])
			->update();

		require_once 'lib/configuratie.include.php';

		$courantModel = CourantRepository::instance();
		$courantBerichtModel = CourantBerichtRepository::instance();
		$couranten = $courantModel->find();

		foreach ($couranten as $courant) {
			$view = new CourantView($courant);

			$courant->inhoud = $view->getHtml(false);

			$courantModel->update($courant);

			$berichten = $courantBerichtModel->getBerichten($courant->id);

			foreach ($berichten as $bericht) {
				$courantBerichtModel->delete($bericht);
			}
		}
	}

	public function down() {
		// sorry
	}
}
