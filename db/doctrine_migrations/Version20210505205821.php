<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210505205821 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Converteer nodige tabellen naar utf8mb4_general_ci';
	}

	public function up(Schema $schema): void
	{

		$this->addSql("SET FOREIGN_KEY_CHECKS=0;");

		$this->addSql('ALTER TABLE document DROP INDEX Zoeken');

		$databaseName = $this->connection->getDatabase();
		// Zet de collation op tabelniveau
		$queries = $this->connection->prepare("
SELECT CONCAT('ALTER TABLE `',  table_name, '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;')
FROM information_schema.TABLES AS T, information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` AS C
WHERE C.collation_name = T.table_collation
AND T.table_schema = '{$databaseName}'
AND
(
    C.CHARACTER_SET_NAME != 'utf8mb4'
    OR
    C.COLLATION_NAME != 'utf8mb4_general_ci'
);
    ")->executeQuery();

		foreach ($queries->iterateColumn() as $query) {
			$this->addSql($query);
		}

		// Zet de collation voor VARCHAR kolommen
		$queries = $this->connection->prepare("
SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY `', column_name, '` ', DATA_TYPE, '(', CHARACTER_MAXIMUM_LENGTH, ') CHARACTER SET utf8mb4', (CASE WHEN COLUMN_DEFAULT IS NOT NULL THEN CONCAT(' DEFAULT ', COLUMN_DEFAULT) ELSE '' END), (CASE WHEN COLUMN_COMMENT IS NOT NULL THEN CONCAT(' COMMENT \'', COLUMN_COMMENT, '\'') ELSE '' END), ' COLLATE utf8mb4_general_ci', (CASE WHEN IS_NULLABLE = 'NO' THEN ' NOT NULL' ELSE '' END), ';')
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = '{$databaseName}'
AND DATA_TYPE = 'varchar'
AND
(
    CHARACTER_SET_NAME != 'utf8mb4'
    OR
    COLLATION_NAME != 'utf8mb4_general_ci'
);")->executeQuery();

		foreach ($queries->iterateColumn() as $query) {
			$this->addSql($query);
		}

		// Zet de collation voor niet-VARCHAR kolommen
		$queries = $this->connection->prepare("
SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY `', column_name, '` ', COLUMN_TYPE, ' CHARACTER SET utf8mb4', (CASE WHEN COLUMN_DEFAULT IS NOT NULL THEN CONCAT(' DEFAULT ', COLUMN_DEFAULT) ELSE '' END), (CASE WHEN COLUMN_COMMENT IS NOT NULL THEN CONCAT(' COMMENT \'', COLUMN_COMMENT, '\'') ELSE '' END), ' COLLATE utf8mb4_general_ci', (CASE WHEN IS_NULLABLE = 'NO' THEN ' NOT NULL' ELSE '' END), ';')
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = '{$databaseName}'
AND DATA_TYPE != 'varchar'
AND
(
    CHARACTER_SET_NAME != 'utf8mb4'
    OR
    COLLATION_NAME != 'utf8mb4_general_ci'
);
")->executeQuery();

		foreach ($queries->iterateColumn() as $query) {
			$this->addSql($query);
		}

		$this->addSql("SET FOREIGN_KEY_CHECKS=1;");
		$this->addSql('ALTER TABLE `document` ADD FULLTEXT INDEX `Zoeken` (`naam`, `filename`);');
	}

	public function down(Schema $schema): void
	{
		$this->throwIrreversibleMigrationException();
	}
}
