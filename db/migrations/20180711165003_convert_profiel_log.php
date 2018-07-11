<?php
require_once 'autoload.php';

use Phinx\Migration\AbstractMigration;

class ConvertProfielLog extends AbstractMigration
{
    public function change()
    {
    	$parser = new \CsrDelft\ProfielLogParser();
    	$serializer = new \Zumba\JsonSerializer\JsonSerializer();
		$dbAdapter = $this->getAdapter();
		$pdo = $dbAdapter->getConnection();
		$results = $this->query('SELECT uid, changelog from profielen')->fetchAll();
		foreach ($results as $row) {
			$parsed = $parser->parse($row['changelog']);
			$statement = $pdo->prepare('UPDATE profielen set changelog=:parsed where uid=:uid');
			$statement->bindValue(':parsed', $serializer->serialize($parsed));
			$statement->bindValue(':uid', $row['uid']);
			$statement->execute();
		}
    }
}
