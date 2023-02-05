<?php

namespace CsrDelft\service\forum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\entity\forum\ForumCategorie;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ForumDelenService
{
	/**
	 * @var ForumDelenRepository
	 */
	private $forumDelenRepository;
	/**
	 * @var ForumDelenMeldingRepository
	 */
	private $forumDelenMeldingRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;
	/**
	 * @var ForumCategorieRepository
	 */
	private $forumCategorieRepository;

	public function __construct(
		EntityManagerInterface $entityManager,
		ForumDelenRepository $forumDelenRepository,
		ForumPostsRepository $forumPostsRepository,
		ForumDradenRepository $forumDradenRepository,
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
		ForumCategorieRepository $forumCategorieRepository,
		ForumDelenMeldingRepository $forumDelenMeldingRepository
	) {
		$this->forumDelenRepository = $forumDelenRepository;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
		$this->entityManager = $entityManager;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumCategorieRepository = $forumCategorieRepository;
	}

	public function verwijderForumDeel($id)
	{
		$this->forumDelenMeldingRepository->stopMeldingenVoorIedereen($id);
		$this->entityManager->remove($this->forumDelenRepository->find($id));
		$this->entityManager->flush();
	}

	/**
	 * Laadt de posts die wachten op goedkeuring en de draadjes en forumdelen die erbij horen.
	 * Check modrechten van gebruiker.
	 *
	 * @return ForumDraad[]
	 * @throws Exception
	 */
	public function getWachtOpGoedkeuring()
	{
		$postsByDraadId = ArrayUtil::group_by(
			'draad_id',
			$this->forumPostsRepository->findBy([
				'wacht_goedkeuring' => true,
				'verwijderd' => false,
			])
		);
		$dradenById = ArrayUtil::group_by_distinct(
			'draad_id',
			$this->forumDradenRepository->findBy([
				'wacht_goedkeuring' => true,
				'verwijderd' => false,
			])
		);
		$dradenById += $this->forumDradenRepository->getForumDradenById(
			array_keys($postsByDraadId)
		); // laad draden bij posts
		foreach ($dradenById as $draad) {
			// laad posts bij draden
			if (array_key_exists($draad->draad_id, $postsByDraadId)) {
				// post is al gevonden
				$draad->setForumPosts($postsByDraadId[$draad->draad_id]);
			} else {
				$melding =
					'Draad ' .
					$draad->draad_id .
					' niet goedgekeurd, maar alle posts wel. Automatische actie: ';
				$draad->wacht_goedkeuring = false;
				if (count($draad->getForumPosts()) === 0) {
					$draad->verwijderd = true;
					$melding .= 'verwijderd (bevat geen berichten)';
					MeldingUtil::setMelding($melding, 2);
				} else {
					$melding .= 'goedgekeurd';
					MeldingUtil::setMelding($melding, 2);
				}
				$this->forumDradenRepository->update($draad);
			}
		}
		// check permissies
		foreach ($dradenById as $draad_id => $draad) {
			if (!$draad->magModereren()) {
				unset($dradenById[$draad_id]);
			}
		}
		if (
			empty($dradenById) &&
			$this->forumPostsRepository->getAantalWachtOpGoedkeuring() > 0
		) {
			MeldingUtil::setMelding(
				'U heeft onvoldoende rechten om de berichten goed te keuren',
				0
			);
		}
		return $dradenById;
	}

	public function getRecent($belangrijk = null)
	{
		$deel = new ForumDeel();
		if ($belangrijk) {
			$deel->titel = 'Belangrijk recent gewijzigd';
		} else {
			$deel->titel = 'Recent gewijzigd';
		}
		$deel->setForumDraden($this->getRecenteForumDraden(null, $belangrijk));
		return $deel;
	}

	/**
	 * Zoek op titel van draadjes en tekst van posts en laad forumdelen die erbij horen.
	 * Check leesrechten van gebruiker.
	 *
	 * @param ForumZoeken $forumZoeken
	 * @param $query
	 * @param $titel
	 * @param $datum
	 * @param $ouder
	 * @param $jaar
	 * @param $per_pagina
	 * @param $offset
	 * @return ForumDraad[]
	 */
	public function zoeken(ForumZoeken $forumZoeken)
	{
		$zoek_in = $forumZoeken->zoek_in;

		$gevonden_draden = [];
		/** @var ForumPost[][] $gevonden_posts */
		$gevonden_posts = [];

		if (in_array('titel', $zoek_in)) {
			foreach (
				$this->forumDradenRepository->zoeken($forumZoeken)
				as [0 => $draad, 'score' => $score]
			) {
				$gevonden_draden[$draad->draad_id] = $draad;
				$draad->score = $score;
			}
		}

		if (in_array('alle_berichten', $zoek_in)) {
			foreach (
				$this->forumPostsRepository->zoeken($forumZoeken, false)
				as [0 => $post, 'score' => $score]
			) {
				$gevonden_posts[$post->draad_id][] = $post;
				$post->score = $score;
			}
		}

		if (in_array('eerste_bericht', $zoek_in)) {
			foreach (
				$this->forumPostsRepository->zoeken($forumZoeken, true)
				as [0 => $post, 'score' => $score]
			) {
				$gevonden_posts[$post->draad_id][] = $post;
				$post->score = $score;
			}
		}

		$gevonden_draden += $this->forumDradenRepository->getForumDradenById(
			array_keys($gevonden_posts)
		);
		// laad draden bij posts

		// laad posts bij draden
		foreach ($gevonden_draden as $draad) {
			if (property_exists($draad, 'score')) {
				// gevonden op draad titel
				$draad->score = (float) 50;
			} else {
				// gevonden op post tekst
				$draad->score = (float) 0;
			}
			if (array_key_exists($draad->draad_id, $gevonden_posts)) {
				// posts al gevonden
				$draad->setForumPosts($gevonden_posts[$draad->draad_id]);
				$draad->laatst_gewijzigd = $this->laatstGewijzigd(
					$gevonden_posts[$draad->draad_id]
				);
				foreach ($draad->getForumPosts() as $post) {
					$draad->score += (float) $post->score;
				}
			} else {
				// laad eerste post
				$array_first_post = $this->forumPostsRepository->getEerstePostVoorDraad(
					$draad
				);
				$draad->setForumPosts([$array_first_post]);
			}
		}
		// check permissies
		foreach ($gevonden_draden as $draad_id => $draad) {
			if (!$draad->magLezen()) {
				unset($gevonden_draden[$draad_id]);
			}
		}
		usort($gevonden_draden, $this->sorteerFunctie($forumZoeken->sorteer_op));

		if ($forumZoeken->sorteer_volgorde == 'asc') {
			$gevonden_draden = array_reverse($gevonden_draden);
		}
		return $gevonden_draden;
	}

	public function laatstGewijzigd($posts)
	{
		return max(
			array_map(function (ForumPost $post) {
				return $post->laatst_gewijzigd;
			}, $posts)
		);
	}

	private function sorteerFunctie($sorteerOp)
	{
		switch ($sorteerOp) {
			case 'aangemaakt_op':
				return function ($a, $b) {
					return $a->datum_tijd < $b->datum_tijd ? 1 : -1;
				};
			case 'laatste_bericht':
				return function ($a, $b) {
					return $a->laatst_gewijzigd < $b->laatst_gewijzigd ? 1 : -1;
				};
			case 'relevantie':
				return function ($a, $b) {
					return $a->score < $b->score ? 1 : -1;
				};
			default:
				throw new CsrGebruikerException('Onbekende sorteermethode');
		}
	}

	/**
	 * Laad recente (niet) (belangrijke) draadjes.
	 * Eager loading van laatste ForumPost
	 * Check leesrechten van gebruiker.
	 * RSS: use token & return delen.
	 *
	 * @param int|null $aantal
	 * @param boolean|null $belangrijk
	 * @param boolean $rss
	 * @param int $offset
	 * @return ForumDraad[]
	 */
	public function getRecenteForumDraden(
		$aantal,
		$belangrijk,
		$rss = false,
		$offset = 0
	) {
		if (!is_int($aantal)) {
			$aantal = $this->forumDradenRepository->getAantalPerPagina();
			$pagina = $this->forumDradenRepository->getHuidigePagina();
			$offset = ($pagina - 1) * $aantal;
		}
		$delenById = $this->forumDelenRepository->getForumDelenVoorLid($rss);
		if (count($delenById) < 1) {
			return [];
		}
		$forum_ids = array_keys($delenById);

		$qb = $this->forumDradenRepository->createQueryBuilder('d');
		$qb->orderBy('d.laatst_gewijzigd', 'DESC');
		$qb->setFirstResult($offset);
		$qb->setMaxResults($aantal);
		$qb->where('d.forum_id in (:forum_ids) or d.forum_id in (:forum_ids)');
		$qb->setParameter('forum_ids', $forum_ids);

		$verbergen = $this->forumDradenVerbergenRepository->findBy([
			'uid' => LoginService::getUid(),
		]);
		$draden_ids = array_keys(
			ArrayUtil::group_by_distinct('draad_id', $verbergen)
		);
		if (count($draden_ids) > 0) {
			$qb->andWhere('d.draad_id not in (:draden_ids)');
			$qb->setParameter('draden_ids', $draden_ids);
		}

		$qb->andWhere('d.wacht_goedkeuring = false and d.verwijderd = false');

		if (is_bool($belangrijk)) {
			if ($belangrijk) {
				$qb->andWhere('d.belangrijk is not null');
			} else {
				if (
					!isset($pagina) ||
					lid_instelling('forum', 'belangrijkBijRecent') === 'nee'
				) {
					$qb->andWhere('d.belangrijk is null');
				}
			}
		}
		$this->forumDradenRepository->filterLaatstGewijzigdExtern($qb);
		$dradenById = ArrayUtil::group_by_distinct(
			'draad_id',
			$qb->getQuery()->getResult()
		);
		$count = count($dradenById);
		if ($count > 0) {
			$draden_ids = array_keys($dradenById);
			array_unshift($draden_ids, LoginService::getUid());
		}
		return $dradenById;
	}

	/**
	 * @return ForumCategorie[]
	 */
	public function getForumIndelingVoorLid()
	{
		$delenByCategorieId = ArrayUtil::group_by(
			'categorie_id',
			$this->forumDelenRepository->getForumDelenVoorLid()
		);
		$indeling = [];
		foreach ($this->forumCategorieRepository->findAll() as $categorie) {
			if ($categorie->magLezen()) {
				$indeling[] = $categorie;
				if (isset($delenByCategorieId[$categorie->categorie_id])) {
					$categorie->setForumDelen(
						$delenByCategorieId[$categorie->categorie_id]
					);
				} else {
					$categorie->setForumDelen([]);
				}
			}
		}
		return $indeling;
	}
}
