<?php

/**
 * TransactieLogModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class TransactieLogModel extends PersistenceModel {

	const ORM = 'TransactieLogEntry';
	const DIR = 'betalen/';

	protected static $instance;

	public function maakTransactieLogEntry($transactie_id, Transactie $transactie, Factuur $factuur) {
		$entry = new TransactieLogEntry();
		$entry->transactie_id = $transactie_id;
		$entry->transactie_serialized = serialize($transactie);
		$entry->factuuur_serialized = serialize($factuur);
		$entry->blockchain_previous_hash = $this->getLastHash();
		$entry->id = (int) $this->create($entry);
		$entry->blockchain_hash = $this->hash($entry);
		$this->update($entry);
		return $entry;
	}

	public function getLastHash() {
		$last = $this->findSparse(array('blockchain_hash'), 'id = MAX(id)', array(), null, null, 1)->fetch();
		if ($last) {
			return $last->blockchain_hash;
		}
		return null;
	}

	private function hash(TransactieLogEntry $entry) {
		return hash('sha512', serialize($entry));
	}

}
