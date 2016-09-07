<?php

/**
 * PersistentAttributeType.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De mogelijke datatypes.
 * 
 */
abstract class T implements PersistentEnum {

	const String = 'varchar(255)';
	const Char = 'char(1)';
	const Boolean = 'tinyint(1)';
	const Integer = 'int(11)';
	const Float = 'float';
	const Date = 'date';
	const Time = 'time';
	const DateTime = 'datetime';
	const Timestamp = 'timestamp';
	const Text = 'text';
	const LongText = 'longtext';
	const Enumeration = 'enum';
	const UID = 'varchar(4)';

	public static function getTypeOptions() {
		return array(self::String, self::Char, self::Boolean, self::Integer, self::Float, self::Date, self::Time, self::DateTime, self::Timestamp, self::Text, self::LongText, self::Enumeration, self::UID);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::String: return 'Tekst (1 zin)';
			case self::Char: return 'Karakter (1 teken)';
			case self::Boolean: return 'Ja/Nee-waarde';
			case self::Integer: return 'Geheel getal';
			case self::Float: return 'Kommagetal';
			case self::Date: return 'Datum';
			case self::Time: return 'Tijd';
			case self::DateTime: return 'Datum & tijd';
			case self::Timestamp: return 'Tijd (getal)';
			case self::Text: return 'Tekst';
			case self::LongText: return 'Tekst (lang)';
			case self::Enumeration: return 'Voorgedefinieerde waarden';
			case self::UID: 'Lidnummer';
			default: throw new Exception('T onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::String: return 's';
			case self::Char: return 'c';
			case self::Boolean: return 'b';
			case self::Integer: return 'i';
			case self::Float: return 'f';
			case self::Date: return 'd';
			case self::Time: return 't';
			case self::DateTime: return 'dt';
			case self::Timestamp: return 'ts';
			case self::Text: return 't';
			case self::LongText: return 'lt';
			case self::Enumeration: return 'e';
			case self::UID: 'u';
			default: throw new Exception('T onbekend');
		}
	}

}
