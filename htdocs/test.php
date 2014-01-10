<?php

try {

	require_once 'configuratie.include.php';
	require_once 'MVC/model/MededelingenModel.class.php';

	$model = new MededelingenModel();

	$m = new Mededeling();

	$model->save($m);

	echo var_dump($m);
	
} catch (Exception $e) {
	echo str_replace('#', '<br />#', $e); // stacktrace
}
?>