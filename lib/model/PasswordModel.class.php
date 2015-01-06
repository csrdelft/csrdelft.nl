<?php

/**
 * PasswordModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class PasswordModel extends PersistenceModel {

	const orm = 'PasswordExpire';

	protected static $instance;

	public function isVerlopen(Lid $lid) {
		$pw = $this->retrieveByPrimaryKey(array($lid->getUid()));
		if (!$pw) {
			return true;
		}
		return time() >= strtotime($pw->expire);
	}

	private function setDatumVerlopen(Lid $lid) {
		$pw = $this->retrieveByPrimaryKey(array($lid->getUid()));
		$create = false;
		if (!$pw) {
			$create = true;
			$pw = new PasswordExpire();
			$pw->uid = $lid->getUid();
		}
		$pw->expire = getDateTime(strtotime(Instellingen::get('beveiliging', 'wachtwoord_verlopen_termijn')));
		if ($create) {
			$this->create($pw);
		} else {
			$this->update($pw);
		}
	}

	public function controleerWachtwoord(Lid $lid, $pass) {
		// Verify SSHA hash
		$ohash = base64_decode(substr($lid->getPassword(), 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($pass . $osalt));
		#echo "ohash: {$ohash}, nhash: {$nhash}";
		if ($ohash === $nhash) {
			return true;
		}
		return false;
	}

	public function maakWachtwoord(Lid $lid, $pass) {
		// Veranderd?
		if (!$this->controleerWachtwoord($lid, $pass)) {
			$this->setDatumVerlopen($lid);
		}
		$salt = mhash_keygen_s2k(MHASH_SHA1, $pass, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass . $salt) . $salt);
	}

}
