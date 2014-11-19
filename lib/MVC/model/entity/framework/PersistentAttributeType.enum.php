<?php

/**
 * PersistentType.enum.php
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
	const Text = 'text';
	const LongText = 'longtext';
	const Enumeration = 'enum';
	const UID = 'varchar(4)';

	public static function getTypeOptions() {
		return array(self::String, self::Char, self::Boolean, self::Integer, self::Float, self::Date, self::Time, self::DateTime, self::Text, self::LongText, self::Enumeration, self::UID);
	}

}
