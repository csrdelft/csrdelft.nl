<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeFunctie;
use CsrDelft\model\entity\maalcie\CorveeKwalificatie;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * KwalificatiesModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class KwalificatiesModel extends CachedPersistenceModel {

	const ORM = CorveeKwalificatie::class;

	/**
	 * Lazy loading of corveefunctie.
	 *
	 * @return CorveeKwalificatie[]
	 */
	public function getAlleKwalificaties() {
		return group_by('functie_id', $this->prefetch());
	}

	public function getKwalificatiesVoorFunctie($fid) {
		return $this->prefetch('functie_id = ?', array($fid));
	}

	/**
	 * Eager loading of corveefuncties.
	 *
	 * @param string $uid
	 * @return CorveeFunctie[]
	 */
	public function getKwalificatiesVanLid($uid) {
		return $this->prefetch('uid = ?', array($uid));
	}

	public function isLidGekwalificeerdVoorFunctie($uid, $fid) {
		return $this->existsByPrimaryKey(array($uid, $fid));
	}

	public function nieuw(CorveeFunctie $functie) {
		$kwalificatie = new CorveeKwalificatie();
		$kwalificatie->functie_id = $functie->functie_id;
		$kwalificatie->wanneer_toegewezen = date('Y-m-d H:i');
		return $kwalificatie;
	}

	public function kwalificatieToewijzen(CorveeKwalificatie $kwali) {
		if ($this->existsByPrimaryKey($kwali->getValues(true))) {
			throw new CsrGebruikerException('Is al gekwalificeerd!');
		}
		$this->create($kwali);
	}

	public function kwalificatieIntrekken($uid, $fid) {
		$rowCount = $this->deleteByPrimaryKey(array($uid, $fid));
		if ($rowCount !== 1) {
			throw new CsrGebruikerException('Is niet gekwalificeerd!');
		}
	}

}
