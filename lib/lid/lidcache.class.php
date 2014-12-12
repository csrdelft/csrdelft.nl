<?php

require_once 'model/CsrMemcache.singleton.php';
require_once 'lid/lid.class.php';

/**
 * lidcache.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * Lid-objecten bewaren in Memcache
 * 
 */
class LidCache {

	/**
	 * Deze methode gebruiken op Lid-objecten te maken. Er wordt dan
	 * automagisch voor de caching gezorgd.
	 */
	public static function getLid($uid) {
		$uid = (string) $uid; // werkomheen int
		if (!Lid::isValidUid($uid)) {
			return false;
		}
		// Kijken of we dit lid al in memcache hebben zitten
		$lid = CsrMemcache::instance()->get($uid);
		if ($lid === false) {
			try {
				// Nieuw lid maken, in memcache stoppen en teruggeven
				$lid = new Lid($uid);
				CsrMemcache::instance()->set($uid, serialize($lid));
				return $lid;
			} catch (Exception $e) {
				if (!startsWith($e->getMessage(), 'Lid [uid:')) {
					DebugLogModel::instance()->log(get_called_class(), 'getLid', func_get_args(), $e);
				}
				return null;
			}
		}
		return unserialize($lid);
	}

	/**
	 * Weggooien van een lid uit de cache.
	 */
	public static function flushLid($uid) {
		$uid = (string) $uid; // werkomheen int
		if (!Lid::isValidUid($uid)) {
			return false;
		}
		return CsrMemcache::instance()->delete($uid);
	}

	/**
	 * Weggooien, en meteen weer opnieuw inladen.
	 */
	public static function updateLid($uid) {
		self::flushLid($uid);
		CsrMemcache::instance()->set($uid, serialize(new Lid($uid)));
		return true;
	}

	public static function flushAll() {
		return CsrMemcache::instance()->flush();
	}

}
