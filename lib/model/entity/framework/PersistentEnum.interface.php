<?php

/**
 * PersistentEnum.interface.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een enumeration heeft type-opties.
 * 
 */
interface PersistentEnum {

	public static function getTypeOptions();

	public static function getDescription($option);

	public static function getChar($option);
}
