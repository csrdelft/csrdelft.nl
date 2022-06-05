<?php

require_once 'generator.enum.php';

try {
	generateEnums();
} catch (Exception $ex) {
	print $ex->getTraceAsString();
}
