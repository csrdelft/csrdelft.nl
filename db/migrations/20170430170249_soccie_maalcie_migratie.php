<?php

use Phinx\Migration\AbstractMigration;

function q($str) {
	return sprintf('"%s"', $str);
}

class SocCieMaalCieMigratie extends AbstractMigration {
	/**
	 * Stop het civisaldo en maalciesaldo bij elkaar in dezelfde tabellen
	 */
	public function up() {
		$this->table('CiviBestelling')
			->addColumn('cie', 'enum', ['values' => ['anders', 'soccie', 'maalcie']])
			->save();
		$this->table('CiviCategorie')
			->changeColumn('cie', 'enum', ['values' => ['anders', 'soccie', 'maalcie']])
			->save();
		$this->execute('ALTER TABLE `CiviBestelling` CHANGE `moment` `moment` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;');

		$prijzen = $this->fetchAll("SELECT van, tot, productId, prijs FROM socCiePrijs;");
		$producten = $this->fetchAll("SELECT id, status, beschrijving, prioriteit, grootboekId, beheer FROM socCieProduct;");
		$grootboeken = $this->fetchAll("SELECT id, type, status FROM socCieGrootboekType;");
		$socciebestellingen = $this->fetchall("SELECT id, socCieId, totaal, tijd, deleted FROM socCieBestelling;");
		$soccieklanten = $this->fetchAll("SELECT socCieId, stekUID, saldo, naam, deleted FROM socCieKlanten;");

		// Houd bij als er geen uid is bij een socciesaldo
		$numNoUid = 0;
		// Voeg soccieklant en civisaldo tabel samen
		for ($i = 0; $i < count($soccieklanten); $i++) {
			if (is_null($soccieklanten[$i]['stekUID'])) {
				$soccieklanten[$i]['new_uid'] = sprintf('c%03d', $numNoUid++);
				$this->execute(sprintf(
					"INSERT INTO CiviSaldo (uid, saldo, naam, laatst_veranderd) VALUES (%s, %d, %s, NOW())",
					q($soccieklanten[$i]['new_uid']),
					$soccieklanten[$i]['saldo'],
					q($soccieklanten[$i]['naam'])
				));

				$soccieklanten[$i]['new_id'] = $this->lastId();
			} else {
				$soccieklanten[$i]['new_uid'] = $soccieklanten[$i]['stekUID'];

				$civisaldo = $this->fetchRow(sprintf(
					"SELECT id, uid, naam, saldo, laatst_veranderd, deleted FROM CiviSaldo WHERE uid = %s",
					q($soccieklanten[$i]['stekUID'])
				));

				$this->execute(sprintf(
					"UPDATE CiviSaldo SET naam = %s, saldo = %d WHERE id = %d",
					q($soccieklanten[$i]['naam']),
					intval($soccieklanten[$i]['saldo']) + intval($civisaldo['saldo']),
					$civisaldo['id']
				));

				$soccieklanten[$i]['new_id'] = $civisaldo['id'];
			}
		}

		// Voeg grootboek en civicategorie tabel samen
		for ($i = 0; $i < count($grootboeken); $i++) {
			$this->execute(sprintf(
				"INSERT INTO CiviCategorie(type, status, cie) VALUES (%s, %s, 'soccie')",
				q($grootboeken[$i]['type']),
				q($grootboeken[$i]['status'])
			));

			$grootboeken[$i]['new_id'] = $this->lastId();
		}

		// Voeg soccieproduct en civiproduct tabel samen
		// Haal het categorie id uit corresponderende grootboek
		for ($i = 0; $i < count($producten); $i++) {
			$grootboek = array_pop(array_filter(
				$grootboeken,
				function ($el) use ($producten, $i) {
					return $el['id'] === $producten[$i]['grootboekId'];
				}
			));

			if (is_null($grootboek)) {
				$grootboek = [
					'new_id' => 2 // Mutatie
				];
			}

			$this->execute(sprintf(
				"INSERT INTO CiviProduct(status, beschrijving, prioriteit, beheer, categorie_id) VALUES (%d, %s, %d, %d, %d)",
				$producten[$i]['status'],
				q($producten[$i]['beschrijving']),
				$producten[$i]['prioriteit'],
				$producten[$i]['beheer'],
				$grootboek['new_id']
			));

			$producten[$i]['new_id'] = $this->lastId();
		}

		// Voeg soccieprijs en civiprijs tabel samen
		// Haal het product id uit corresponderende product
		for ($i = 0; $i < count($prijzen); $i++) {
			$product = array_pop(array_filter(
				$producten,
				function ($el) use ($prijzen, $i) {
					return $el['id'] === $prijzen[$i]['productId'];
				}
			));

			$tot = new DateTime($prijzen[$i]['tot']);
			if ($tot->format("Y") >= 2035)
				$tot = null;

			$this->execute(sprintf(
				"INSERT INTO CiviPrijs(van, tot, product_id, prijs) VALUES (%s, %s, %d, %d)",
				q($prijzen[$i]['van']),
				isset($tot) ? q($prijzen[$i]['tot']) : 'NULL',
				$product['new_id'],
				$prijzen[$i]['prijs']
			));
		}

		// Voeg socciebestelling en civibestelling tabel samen
		// Haal het uid uit corresponderende soccieklant
		for ($i = 0; $i < count($socciebestellingen); $i++) {
			$soccieklant = array_pop(array_filter(
				$soccieklanten,
				function ($el) use ($socciebestellingen, $i) {
					return $el['socCieId'] === $socciebestellingen[$i]['socCieId'];
				}
			));

			$this->execute(sprintf(
				"INSERT INTO CiviBestelling (uid, totaal, deleted, moment, cie) VALUES (%s, %d, %d, %s, 'soccie')",
				q($soccieklant['new_uid']),
				$socciebestellingen[$i]['totaal'],
				$socciebestellingen[$i]['deleted'],
				q($socciebestellingen[$i]['tijd'])
			));

			$socciebestellingen[$i]['new_id'] = $this->lastId();

			$socciebestellinginhoud = $this->fetchAll(sprintf(
				"SELECT bestellingId, productId, aantal FROM socCieBestellingInhoud WHERE bestellingId = %s; ",
				$socciebestellingen[$i]['id']
			));

			// Voeg socciebestellinginhoud en civibestellinginhoud tabel samen
			// Haal product id uit corresponderende product
			// Haal bestelling id uit bovenstaande bestelling
			for ($j = 0; $j < count($socciebestellinginhoud); $j++) {
				$item = $socciebestellinginhoud[$j];
				$product = array_pop(array_filter(
					$producten,
					function ($el) use ($item) {
						return $el['id'] == $item['productId'];
					}
				));

				$this->execute(sprintf(
					"INSERT INTO CiviBestellingInhoud (bestelling_id, product_id, aantal) VALUES (%d, %d, %d);",
					$socciebestellingen[$i]['new_id'],
					$product['new_id'],
					$item['aantal']
				));
			}
		}

		//$this->adapter->commitTransaction();
	}

	public function down() {
		try{
			$this->table('CiviBestelling')
				->removeColumn('cie')
				->save();
		} catch (Exception $ignored) { echo "CiviBestelling.cie kolom is al verwijderd\n"; }
	}

	private function lastId() {
		return $this->fetchRow("SELECT LAST_INSERT_ID();")[0];
	}
}
