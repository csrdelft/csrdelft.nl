<?php
require "configuratie.include.php";

use CsrDelft\model\security\LoginModel;




if (!LoginModel::mag('P_ADMIN')) {
	redirect(CSR_ROOT);
}

$p = new \CsrDelft\ProfielLogParser();

header('Content-Type: application/json');
if (isset($_GET["convert"])) {
	$ser = new \Zumba\JsonSerializer\JsonSerializer();
	foreach (\CsrDelft\model\ProfielModel::instance()->find() as $prof) {
		$parsed = $p->parse($prof->changelog);
		$prof->changelog = $ser->serialize($parsed);
		\CsrDelft\model\ProfielModel::instance()->update($prof);

	}
} else if (isset($_GET["uid"]))
	echo json_encode($p->parse(\CsrDelft\model\ProfielModel::get($_GET["uid"])->changelog), JSON_PRETTY_PRINT);
else {
	$list = [];
	foreach (\CsrDelft\model\ProfielModel::instance()->find() as $prof) {
		$parsed = $p->parse($prof->changelog);
		if ($parsed === null) {
			$list[] = [uid => $prof->uid, log => $prof->changelog];
		}
	}
	echo json_encode($list, JSON_PRETTY_PRINT);
}

