<?php

/**
 * FinancienStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Financiele status.
 * 
 */
abstract class HappieFinancienStatus implements PersistentEnum {

	const Nieuw = 'Nieuw';
	const Gewijzigd = 'Gewijzigd';
	const KassaIngevoerd = 'Kassa Ingevoerd';
	const Voldaan = 'Voldaan';

	public static function getTypeOptions() {
		return array(self::Nieuw, self::Gewijzigd, self::KassaIngevoerd, self::Voldaan);
	}

	public static function getSelectOptions() {
		$options = array();
		foreach (self::getTypeOptions() as $option) {
			$options[$option] = $option;
		}
		unset($options[self::Nieuw]);
		return $options;
	}

}
