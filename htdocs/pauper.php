<?php

// pauper.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)

require_once 'configuratie.include.php';

$_SESSION['pauper'] = true;

require_once 'MVC/model/CmsPaginaModel.class.php';
require_once 'MVC/view/CmsPaginaView.class.php';
$pagina = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('mobiel'));
$pagina->view();

# Laatste forumberichten
require_once 'MVC/model/ForumModel.class.php';
require_once 'MVC/view/ForumView.class.php';
$forum = new ForumDeelView(ForumDelenModel::instance()->getRecent());
$forum->view();
