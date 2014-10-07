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

	const String = 'string';
	const Char = 'char';
	const Boolean = 'boolean';
	const Integer = 'int';
	const Float = 'float';
	const DateTime = 'datetime';
	const Text = 'text';
	const LongText = 'longtext';
	const Enumeration = 'enum';
	const UID = 'uid';

	public static function getTypeOptions() {
		return array(self::String, self::Char, self::Boolean, self::Integer, self::Float, self::DateTime, self::Text, self::LongText, self::Enumeration, self::UID);
	}

}
