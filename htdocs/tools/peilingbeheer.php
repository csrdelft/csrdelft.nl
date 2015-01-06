<?php

require_once 'configuratie.include.php';
require_once 'view/PeilingenView.class.php';

/**
 *  Peiling beheerpagina
 */
$error = '';
if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'toevoegen':
			if (isset($_POST['titel'], $_POST['opties']) AND PeilingenModel::magBewerken()) {
				$properties['titel'] = $_POST['titel'];
				$properties['verhaal'] = $_POST['verhaal'];
				$properties['opties'] = array();
				foreach ($_POST['opties'] as $optie) {
					if (trim($optie) != '') {
						$properties['opties'][] = trim($optie);
					}
				}
				$peiling = PeilingenModel::maakPeiling($properties);
				setMelding('De nieuwe peiling heeft id ' . $peiling->getId() . '.', 1);
				redirect('/tools/peilingbeheer.php');
				exit;
			}
			break;
		case 'stem':
			if (isset($_POST['id'])) {
				try {
					$peiling = new PeilingenModel((int) $_POST['id']);
				} catch (Exception $e) {
					$error = $e->getMessage();
				}

				if (isset($_POST['optie']) && is_numeric($_POST['optie'])) {
					$peiling->stem((int) $_POST['optie']);
				}
				redirect(HTTP_REFERER . '#peiling' . $peiling->getId());
			}
			break;
		case 'verwijder':
			if (isset($_GET['id']) AND PeilingenModel::magBewerken()) {
				try {
					$peiling = new PeilingenModel((int) $_GET['id']);
					$peiling->deletePeiling();
					redirect(HTTP_REFERER);
				} catch (Exception $e) {
					$error = $e->getMessage();
				}
			}
			break;
	}
}

$beheer = new PeilingenBeheerView(PeilingenModel::getLijst());

if ($error != '') {
	setMelding($error, -1);
}

if (!LoginModel::mag('P_LOGGED_IN') OR ! PeilingenModel::magBewerken()) {
	# geen rechten
	require_once 'model/CmsPaginaModel.class.php';
	require_once 'view/CmsPaginaView.class.php';
	$beheer = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
}

$pagina = new CsrLayoutPage($beheer);
$pagina->addCompressedResources('peilingbeheer');
$pagina->view();
