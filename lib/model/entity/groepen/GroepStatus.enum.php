<?php

/**
 * GroepStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De status van een groep: f.t. / h.t. / o.t.
 * 
 */
abstract class GroepStatus implements PersistentEnum {

	const OT = 'ot';
	const HT = 'ht';
	const FT = 'ft';

	public static function getTypeOptions() {
		return array(self::OT, self::HT, self::FT);
	}

}
