<?php

try {

	require_once 'configuratie.include.php';

	/*
	$mail = new Mail('brussee@live.nl','brussee@live.nl','test');
	$mail->send();
	
	/*
	$model = MededelingenModel::instance();

	$m1 = new Mededeling2();
	echo var_dump($m1);
	
	$model->save($m1);
	$id = $m1->id;
	unset($m1);
	
	$m2 = $model->fetch($id);
	echo var_dump($m2);
	
	$model->remove($id);
	*/
} catch (Exception $e) {
	echo str_replace('#', '<br />#', $e); // stacktrace
}
