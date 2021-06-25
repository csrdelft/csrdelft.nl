<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200705192721 extends AbstractMigration {
	public function getDescription(): string {
		return 'Hernoem tabellen zodat er geen hoofdletters meer in zitten.';
	}

	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE CiviProduct DROP INDEX IDX_C4590238BCF5E72D, ADD INDEX IDX_C63728B3BCF5E72D (categorie_id)');
		$this->addSql('ALTER TABLE CiviBestellingInhoud DROP INDEX IDX_30A7C75CA2E63037, ADD INDEX IDX_E800F85CA2E63037 (bestelling_id)');
		$this->addSql('ALTER TABLE CiviBestellingInhoud DROP INDEX IDX_30A7C75C4584665A, ADD INDEX IDX_E800F85C4584665A (product_id)');
		$this->addSql('ALTER TABLE CiviBestelling DROP INDEX IDX_290D88AC539B0606, ADD INDEX IDX_10CFCB8539B0606 (uid)');
		$this->addSql('ALTER TABLE CiviPrijs DROP INDEX IDX_86CCDFA74584665A, ADD INDEX IDX_89AF10254584665A (product_id)');
		$this->addSql('ALTER TABLE voorkeurCommissie DROP INDEX IDX_6567316BCF5E72D, ADD INDEX IDX_8E4EA8E8BCF5E72D (categorie_id)');
		$this->addSql('ALTER TABLE voorkeurVoorkeur DROP INDEX IDX_1A129E32539B0606, ADD INDEX IDX_A9386EA539B0606 (uid)');
		$this->addSql('ALTER TABLE voorkeurVoorkeur DROP INDEX IDX_1A129E324B30D9C4, ADD INDEX IDX_A9386EA4B30D9C4 (cid)');
		$this->addSql('ALTER TABLE Document DROP INDEX IDX_211FE820BCF5E72D, ADD INDEX IDX_D8698A76BCF5E72D (categorie_id)');
		$this->addSql('ALTER TABLE Document DROP INDEX IDX_211FE820F725D48E, ADD INDEX IDX_D8698A76F725D48E (eigenaar)');

		// Document kan niet worden hernoemd op Windows, moet met een tussenstap.
		$this->addSql(<<<SQL
RENAME TABLE
	CiviBestellingInhoud TO civi_bestelling_inhoud,
	CiviBestelling TO civi_bestelling,
	CiviCategorie TO civi_categorie,
	CiviLog TO civi_saldo_log,
	CiviPrijs TO civi_prijs,
	CiviProduct TO civi_product,
	CiviSaldo TO civi_saldo,
	Document TO document_,
	DocumentCategorie TO document_categorie,
	voorkeurVoorkeur TO voorkeur_voorkeur,
	voorkeurCommissie TO voorkeur_commissie,
	voorkeurOpmerking TO voorkeur_opmerking,
	voorkeurCommissieCategorie TO voorkeur_commissie_categorie,
	GoogleToken TO google_token;
SQL
		);
		$this->addSql('RENAME TABLE document_ TO document;');

	}

	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE civi_product DROP INDEX IDX_C63728B3BCF5E72D, ADD INDEX IDX_C4590238BCF5E72D (categorie_id)');
		$this->addSql('ALTER TABLE civi_bestelling_inhoud DROP INDEX IDX_E800F85CA2E63037, ADD INDEX IDX_30A7C75CA2E63037 (bestelling_id)');
		$this->addSql('ALTER TABLE civi_bestelling_inhoud DROP INDEX IDX_E800F85C4584665A, ADD INDEX IDX_30A7C75C4584665A (product_id)');
		$this->addSql('ALTER TABLE civi_bestelling DROP INDEX IDX_10CFCB8539B0606, ADD INDEX IDX_290D88AC539B0606 (uid)');
		$this->addSql('ALTER TABLE civi_prijs DROP INDEX IDX_89AF10254584665A, ADD INDEX IDX_86CCDFA74584665A (product_id)');
		$this->addSql('ALTER TABLE voorkeur_commissie DROP INDEX IDX_8E4EA8E8BCF5E72D, ADD INDEX IDX_6567316BCF5E72D (categorie_id)');
		$this->addSql('ALTER TABLE voorkeur_voorkeur DROP INDEX IDX_A9386EA539B0606, ADD INDEX IDX_1A129E32539B0606 (uid)');
		$this->addSql('ALTER TABLE voorkeur_voorkeur DROP INDEX IDX_A9386EA4B30D9C4, ADD INDEX IDX_1A129E324B30D9C4 (cid)');
		$this->addSql('ALTER TABLE document DROP INDEX IDX_D8698A76BCF5E72D, ADD INDEX IDX_211FE820BCF5E72D (categorie_id)');
		$this->addSql('ALTER TABLE Document DROP INDEX IDX_D8698A76F725D48E, ADD INDEX IDX_211FE820F725D48E (eigenaar)');

		$this->addSql('RENAME TABLE document TO document_;');
		$this->addSql(<<<SQL
RENAME TABLE
	civi_bestelling_inhoud TO CiviBestellingInhoud,
	civi_bestelling TO CiviBestelling,
	civi_categorie TO CiviCategorie,
	civi_saldo_log TO CiviLog,
	civi_prijs TO CiviPrijs,
	civi_product TO CiviProduct,
	civi_saldo TO CiviSaldo,
	document_ TO Document,
	document_categorie TO DocumentCategorie,
	voorkeur_voorkeur TO voorkeurVoorkeur,
	voorkeur_commissie TO voorkeurCommissie,
	voorkeur_opmerking TO voorkeurOpmerking,
	voorkeur_commissie_categorie TO voorkeurCommissieCategorie,
	google_token TO GoogleToken;
SQL
		);
	}
}
