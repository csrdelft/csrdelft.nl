<?php

/**
 * PersistentEnum.interface.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De keuzesoort van een selector: AND / XOR
 * 
 */
interface PersistentEnum {

	public static function values();

	public static function getMaxLenght();
}
