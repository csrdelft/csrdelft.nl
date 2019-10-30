<?php
require dirname(__DIR__) . '/lib/configuratie.include.php';

use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumPost;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\mededelingen\MededelingenModel;
use CsrDelft\model\security\LoginModel;
use Phinx\Migration\AbstractMigration;

// Archiveer mededelingen in een forumdraad
$archiveDraad = new ForumDraad();
$archiveDraad->uid = 'x900';
$archiveDraad->titel = "Archief mededelingen";
$archiveDraad->forum_id = 1;
$archiveDraad->datum_tijd = time();
$archiveDraad->gesloten = true;
$archiveDraad->verwijderd = false;
$archiveDraad->wacht_goedkeuring = false;
$archiveDraad->plakkerig = false;
$archiveDraad->eerste_post_plakkerig = false;
$archiveDraad->pagina_per_post = 0;
$id = ForumDradenModel::instance()->create($archiveDraad);

foreach (MededelingenModel::instance()->find() as $mededeling) {
	/**
	 * @var $mededeling \CsrDelft\model\entity\mededelingen\Mededeling
	 */
	$post = new ForumPost();
	$post->tekst = $mededeling->tekst;
	$post->uid = $mededeling->uid;
	$post->datum_tijd = $mededeling->datum;
	$post->draad_id = $id;
	$post->laatst_gewijzigd = $mededeling->datum;
	$post->verwijderd = false;
	$post->auteur_ip = '127.0.0.1';
	$post->wacht_goedkeuring = false;
	ForumPostsModel::instance()->create($post);
}
