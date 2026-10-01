<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261001184701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Strip [taal=en] bb-tags met content en [taal=nl] tags maar behoudt content';
    }

    public function up(Schema $schema): void
    {
			$rows = $this->connection->fetchAllAssociative(
				"SELECT naam, inhoud FROM cms_paginas
             	WHERE inhoud LIKE '%[taal=en]%' OR inhoud LIKE '%[taal=nl]%'"
			);

			foreach ($rows as $row) {
				$content = (string) $row['inhoud'];

				// Verwijder [taal=en]...[/taal] inclusief inhoud
				$new = preg_replace('/\[taal=en\].*?\[\/taal\]/si', '', $content);

				// Behoud inhoud van [lang=nl]...[/lang], verwijder tags
				$new = preg_replace('/\[taal=nl\](.*?)\[\/taal\]/si', '$1', $new);

				if ($new === null) {
					throw new \RuntimeException(sprintf('Regex failed for cms_pagina with naam = %s', $row['naam']));
				}

				if ($new !== $content) {
					$this->addSql(
						'UPDATE cms_paginas SET inhoud = :inhoud WHERE naam = :naam',
						['inhoud' => $new, 'naam' => $row['naam']]
					);
				}
			}
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
