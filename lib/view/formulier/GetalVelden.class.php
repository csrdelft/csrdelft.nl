<?php

/**
 * GetalVelden.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Bevat de uitbreidingen van TextField:
 * 
 * 	- IntField					Integers 
 * 		* FloatField				Kommagetallen
 * 			- BedragField			Bedragen met 2 cijfers achter de komma
 * 	- TelefoonField				Telefoonnummers
 */

require_once 'view/formulier/getalvelden/BedragField.class.php';
require_once 'view/formulier/getalvelden/FloatField.class.php';
require_once 'view/formulier/getalvelden/IntField.class.class.php';
require_once 'view/formulier/getalvelden/RequiredBedragField.class.php';
require_once 'view/formulier/getalvelden/RequiredFloatField.class.php';
require_once 'view/formulier/getalvelden/RequiredIntField.class.php';
require_once 'view/formulier/getalvelden/RequiredTelefoonField.class.php';
require_once 'view/formulier/getalvelden/TelefoonField.class.php';
