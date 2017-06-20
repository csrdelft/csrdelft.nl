<?php
namespace CsrDelft\model\forum;
use function CsrDelft\getDateTime;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadGelezen;
use CsrDelft\model\entity\forum\ForumPost;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\Paging;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;
use function CsrDelft\startsWith;
use CsrDelft\view\bbcode\CsrBB;
use Exception;
use PDO;

/**
 * ForumPostsModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumPostsModel extends CachedPersistenceModel implements Paging {

	const ORM = ForumPost::class;
	const DIR = 'forum/';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'datum_tijd ASC';
	/**
	 * Huidige pagina
	 * @var int
	 */
	private $pagina;
	/**
	 * Aantal posts per pagina
	 * @var int
	 */
	private $per_pagina;
	/**
	 * Totaal aantal paginas per forumdraad
	 * @var int[]
	 */
	private $aantal_paginas;
	/**
	 * Totaal aantal posts die wachten op goedkeuring
	 * @var int
	 */
	private $aantal_wacht;

	/**
	 * @param $id
	 * @return ForumPost
	 * @throws Exception
	 */
	public static function get($id) {
		$post = static::instance()->retrieveByPrimaryKey(array($id));
		if (!$post) {
			throw new Exception('Forum-reactie bestaat niet!');
		}
		return $post;
	}

	protected function __construct() {
		CachedPersistenceModel::__construct();
		$this->pagina = 1;
		$this->per_pagina = (int)LidInstellingenModel::get('forum', 'posts_per_pagina');
		$this->aantal_paginas = array();
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function setAantalPerPagina($aantal) {
		$this->per_pagina = (int)$aantal;
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($pagina, $draad_id) {
		if (!is_int($pagina) OR $pagina < 1) {
			$pagina = 1;
		} elseif ($draad_id !== 0 AND $pagina > $this->getAantalPaginas($draad_id)) {
			$pagina = $this->getAantalPaginas($draad_id);
		}
		$this->pagina = $pagina;
	}

	public function setLaatstePagina($draad_id) {
		$this->pagina = $this->getAantalPaginas($draad_id);
	}

	public function getAantalPaginas($draad_id) {
		if (!array_key_exists($draad_id, $this->aantal_paginas)) {
			$draad = ForumDradenModel::get($draad_id);
			if ($draad->pagina_per_post) {
				$this->per_pagina = 1;
			} else {
				$this->per_pagina = (int)LidInstellingenModel::get('forum', 'posts_per_pagina');
			}
			$this->aantal_paginas[$draad_id] = (int)ceil($this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id)) / $this->per_pagina);
		}
		return max(1, $this->aantal_paginas[$draad_id]);
	}

	public function getPaginaVoorPost(ForumPost $post) {
		$count = $this->count('draad_id = ? AND post_id <= ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($post->draad_id, $post->post_id));
		return (int)ceil($count / $this->per_pagina);
	}

	public function setPaginaVoorLaatstGelezen(ForumDraadGelezen $gelezen) {
		$count = 1 + $this->count('draad_id = ? AND datum_tijd <= ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($gelezen->draad_id, $gelezen->datum_tijd));
		$this->getAantalPaginas($gelezen->draad_id); // set per_pagina
		$this->setHuidigePagina((int)ceil($count / $this->per_pagina), $gelezen->draad_id);
	}

	public function zoeken($query, $datumsoort, $ouder, $jaar, $limit) {
		$this->per_pagina = (int)$limit;
		$attributes = array('*', 'MATCH(tekst) AGAINST (? IN NATURAL LANGUAGE MODE) AS score');
		$where = 'wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		$where_params = array($query);
		$order = 'score DESC';
		if (in_array($datumsoort, array('datum_tijd', 'laatst_gewijzigd'))) {
			$order .= ', ' . $datumsoort . ' DESC';
			if (is_int($jaar)) {
				$where .= ' AND ' . $datumsoort;
				if ($ouder === 'ouder') {
					$where .= ' < ?';
				} else {
					$where .= ' > ?';
				}
				$where_params[] = getDateTime(strtotime('-' . $jaar . ' year'));
			}
		}
		$where .= ' HAVING score > 0';
		$results = Database::instance()->sqlSelect($attributes, $this->getTableName(), $where, $where_params, null, $order, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$results->setFetchMode(PDO::FETCH_CLASS, static::ORM, array($cast = true));
		return $results;
	}

	public function getAantalForumPostsVoorLid($uid) {
		return $this->count('uid = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($uid));
	}

	public function getAantalWachtOpGoedkeuring() {
		if (!isset($this->aantal_wacht)) {
			$this->aantal_wacht = $this->count('wacht_goedkeuring = TRUE AND verwijderd = FALSE');
		}
		return $this->aantal_wacht;
	}

	public function getPrullenbakVoorDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ? AND verwijderd = TRUE', array($draad->draad_id));
	}

	public function getForumPostsVoorDraad(ForumDraad $draad) {
		if (LoginModel::mag('P_FORUM_MOD')) {
			$goedkeuring = '';
		} else {
			$goedkeuring = ' AND wacht_goedkeuring = FALSE';
		}
		$posts = $this->prefetch('draad_id = ?' . $goedkeuring . ' AND verwijderd = FALSE', array($draad->draad_id), null, null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		if ($draad->eerste_post_plakkerig AND $this->pagina !== 1) {
			$first_post = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, null, 1)->fetch();
			array_unshift($posts, $first_post);
		}
		// 2008-filter
		if (LidInstellingenModel::get('forum', 'filter2008') == 'ja') {
			foreach ($posts as $post) {
				if (startsWith($post->uid, '08')) {
					$post->gefilterd = 'Bericht van 2008';
				}
			}
		}
		return $posts;
	}

	/**
	 * Laad de meest recente forumposts van een gebruiker.
	 * Check leesrechten van gebruiker.
	 *
	 * @param string $uid
	 * @param int $aantal
	 * @param int $draad_uniek
	 * @return ForumPost[]
	 */
	public function getRecenteForumPostsVanLid($uid, $aantal, $draad_uniek = false) {
		$where = 'uid = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		$posts = array();
		$draden_ids = array();
		foreach ($this->find($where, array($uid), $draad_uniek ? 'draad_id' : null, 'laatst_gewijzigd DESC', $aantal) as $post) {
			if ($post->getForumDraad()->magLezen()) {
				$posts[] = $post;
				$draden_ids[] = $post->draad_id;
			}
		}
		$count = count($draden_ids);
		if ($count > 0) {
			array_unshift($draden_ids, LoginModel::getUid());
			ForumDradenGelezenModel::instance()->prefetch('uid = ? AND draad_id IN (' . implode(', ', array_fill(0, $count, '?')) . ')', $draden_ids);
		}
		return $posts;
	}

	public function maakForumPost($draad_id, $tekst, $ip, $wacht_goedkeuring, $email) {
		$post = new ForumPost();
		$post->draad_id = (int)$draad_id;
		$post->uid = LoginModel::getUid();
		$post->tekst = $tekst;
		$post->datum_tijd = getDateTime();
		$post->laatst_gewijzigd = $post->datum_tijd;
		$post->bewerkt_tekst = null;
		$post->verwijderd = false;
		$post->auteur_ip = $ip;
		$post->wacht_goedkeuring = $wacht_goedkeuring;
		if ($wacht_goedkeuring) {
			$post->bewerkt_tekst = '[prive]email: [email]' . $email . '[/email][/prive]' . "\n";
		}
		$post->post_id = (int)$this->create($post);
		return $post;
	}

	public function verwijderForumPost(ForumPost $post) {
		$post->verwijderd = !$post->verwijderd;
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Verwijderen mislukt');
		}
		ForumDradenModel::instance()->resetLastPost($post->getForumDraad());
	}

	public function verwijderForumPostsVoorDraad(ForumDraad $draad) {
		Database::instance()->sqlUpdate($this->getTableName(), array('verwijderd' => $draad->verwijderd), 'draad_id = :id', array(':id' => $draad->draad_id));
	}

	public function bewerkForumPost($nieuwe_tekst, $reden, ForumPost $post) {
		$verschil = levenshtein($post->tekst, $nieuwe_tekst);
		$post->tekst = $nieuwe_tekst;
		$post->laatst_gewijzigd = getDateTime();
		$bewerkt = 'bewerkt door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]';
		if ($reden !== '') {
			$bewerkt .= ': [tekst]' . CsrBB::escapeUbbOff($reden) . '[/tekst]';
		}
		$bewerkt .= "\n";
		$post->bewerkt_tekst .= $bewerkt;
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Bewerken mislukt');
		}
		if ($verschil > 3) {
			$draad = $post->getForumDraad();
			$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
			$draad->laatste_post_id = $post->post_id;
			$draad->laatste_wijziging_uid = $post->uid;
			$rowCount = ForumDradenModel::instance()->update($draad);
			if ($rowCount !== 1) {
				throw new Exception('Bewerken mislukt');
			}
		}
	}

	public function verplaatsForumPost(ForumDraad $nieuwDraad, ForumPost $post) {
		$oudeDraad = $post->getForumDraad();
		$post->draad_id = $nieuwDraad->draad_id;
		$post->laatst_gewijzigd = getDateTime();
		$post->bewerkt_tekst .= 'verplaatst door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]' . "\n";
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Verplaatsen mislukt');
		}
		ForumDradenModel::instance()->resetLastPost($post->getForumDraad());
		ForumDradenModel::instance()->resetLastPost($oudeDraad);
	}

	public function offtopicForumPost(ForumPost $post) {
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_gewijzigd = getDateTime();
		$post->bewerkt_tekst .= 'offtopic door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]' . "\n";
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Offtopic mislukt');
		}
	}

	public function goedkeurenForumPost(ForumPost $post) {
		if ($post->wacht_goedkeuring) {
			$post->wacht_goedkeuring = false;
			$post->laatst_gewijzigd = getDateTime();
			$post->bewerkt_tekst .= '[prive=P_FORUM_MOD]Goedgekeurd door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate][/prive]' . "\n";
			$rowCount = $this->update($post);
			if ($rowCount !== 1) {
				throw new Exception('Goedkeuren mislukt');
			}
		}
		$draad = $post->getForumDraad();
		$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
		$draad->laatste_post_id = $post->post_id;
		$draad->laatste_wijziging_uid = $post->uid;
		if ($draad->wacht_goedkeuring) {
			$draad->wacht_goedkeuring = false;
		}
		ForumDradenModel::instance()->update($draad);
	}

	public function citeerForumPost(ForumPost $post) {
		$tekst = CsrBB::filterCommentaar(CsrBB::filterPrive($post->tekst));
		return '[citaat=' . $post->uid . ']' . CsrBB::sluitTags($tekst) . '[/citaat]';
	}

	public function getStatsTotal() {
		$terug = getDateTime(strtotime(InstellingenModel::get('forum', 'grafiek_stats_periode')));
		$fields = array('UNIX_TIMESTAMP(DATE(datum_tijd)) AS timestamp', 'COUNT(*) AS count'); // flot date format
		return Database::instance()->sqlSelect($fields, $this->getTableName(), 'datum_tijd > ?', array($terug), 'timestamp');
	}

	public function getStatsVoorForumDeel(ForumDeel $deel) {
		$terug = getDateTime(strtotime(InstellingenModel::get('forum', 'grafiek_stats_periode')));
		$fields = array('UNIX_TIMESTAMP(DATE(p.datum_tijd)) AS timestamp', 'COUNT(*) AS count'); // flot date format
		return Database::instance()->sqlSelect($fields, $this->getTableName() . ' AS p RIGHT JOIN ' . ForumDradenModel::instance()->getTableName() . ' AS d ON p.draad_id = d.draad_id', 'd.forum_id = ? AND p.datum_tijd > ?', array($deel->forum_id, $terug), 'timestamp');
	}

	public function getStatsVoorDraad(ForumDraad $draad) {
		$terug = getDateTime(strtotime(InstellingenModel::get('forum', 'grafiek_draad_recent'), strtotime($draad->laatst_gewijzigd)));
		$fields = array('UNIX_TIMESTAMP(DATE(datum_tijd)) AS timestamp', 'COUNT(*) AS count'); // flot date format
		return Database::instance()->sqlSelect($fields, $this->getTableName(), 'draad_id = ? AND datum_tijd > ?', array($draad->draad_id, $terug), 'timestamp');
	}

}