<?php


namespace CsrDelft\view\formulier;


use CsrDelft\view\formulier\invoervelden\DoctrineEntityField;

/**
 * Interface DisplayEntity
 * @package CsrDelft\view\formulier
 * @see DoctrineEntityField
 */
interface DisplayEntity
{
	function getId();

	function getWeergave(): string;
}
