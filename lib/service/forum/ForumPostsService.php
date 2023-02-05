<?php

namespace CsrDelft\service\forum;

use CsrDelft\common\CsrException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\CsrBB;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class ForumPostsService
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;
	/**
	 * @var ForumDradenMeldingRepository
	 */
	private $forumDradenMeldingRepository;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDradenReagerenRepository
	 */
	private $forumDradenReagerenRepository;
	/**
	 * @var ForumMeldingenService
	 */
	private $forumMeldingenService;

	public function __construct(
		EntityManagerInterface $entityManager,
		ForumDradenRepository $forumDradenRepository,
		ForumDradenMeldingRepository $forumDradenMeldingRepository,
		ForumPostsRepository $forumPostsRepository,
		ForumMeldingenService $forumMeldingenService,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository
	) {
		$this->entityManager = $entityManager;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumDradenMeldingRepository = $forumDradenMeldingRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumMeldingenService = $forumMeldingenService;
	}

	public function verplaatsForumPost(ForumDraad $nieuwDraad, ForumPost $post)
	{
		$oudeDraad = $post->draad;
		$post->draad = $nieuwDraad;
		$post->laatst_gewijzigd = date_create_immutable();
		$post->bewerkt_tekst .=
			'verplaatst door [lid=' .
			LoginService::getUid() .
			'] [reldate]' .
			DateUtil::dateFormatIntl(
				$post->laatst_gewijzigd,
				DateUtil::DATETIME_FORMAT
			) .
			'[/reldate]' .
			"\n";
		$this->entityManager->persist($post);
		$this->entityManager->flush();

		$this->resetLastPost($post->draad);
		if (count($oudeDraad->getForumPosts()) == 0) {
			$this->wijzigForumDraad($oudeDraad, 'verwijderd', true);
		} else {
			$this->resetLastPost($oudeDraad);
		}
	}

	public function wijzigForumDraad(ForumDraad $draad, $property, $value)
	{
		if (!property_exists($draad, $property)) {
			throw new CsrException('Property undefined: ' . $property);
		}
		$draad->$property = $value;

		$this->entityManager->persist($draad);
		$this->entityManager->flush();

		if ($property === 'belangrijk') {
			$this->forumDradenVerbergenRepository->toonDraadVoorIedereen([
				$draad->draad_id,
			]);
		} elseif ($property === 'gesloten') {
			$this->forumDradenMeldingRepository->stopMeldingenVoorIedereen([
				$draad->draad_id,
			]);
		} elseif ($property === 'verwijderd') {
			$this->forumDradenMeldingRepository->stopMeldingenVoorIedereen([
				$draad->draad_id,
			]);
			$this->forumDradenVerbergenRepository->toonDraadVoorIedereen([
				$draad->draad_id,
			]);
			$this->forumDradenGelezenRepository->verwijderDraadGelezen([
				$draad->draad_id,
			]);
			$this->forumDradenReagerenRepository->verwijderReagerenVoorDraad([
				$draad->draad_id,
			]);
			$this->forumPostsRepository->verwijderForumPostsVoorDraad($draad);
		}
	}

	public function verwijderForumPost(ForumPost $post)
	{
		$post->verwijderd = !$post->verwijderd;
		$this->entityManager->persist($post);
		$this->entityManager->flush();

		$this->resetLastPost($post->draad);
	}

	public function bewerkForumPost($nieuwe_tekst, $reden, ForumPost $post)
	{
		similar_text($post->tekst, $nieuwe_tekst, $gelijkheid);
		$post->tekst = $nieuwe_tekst;
		$post->laatst_gewijzigd = date_create_immutable();
		$bewerkt =
			'bewerkt door [lid=' .
			LoginService::getUid() .
			'] [reldate]' .
			DateUtil::dateFormatIntl(
				$post->laatst_gewijzigd,
				DateUtil::DATETIME_FORMAT
			) .
			'[/reldate]';
		if ($reden !== '') {
			$bewerkt .= ': [tekst]' . CsrBB::escapeUbbOff($reden) . '[/tekst]';
		}
		$bewerkt .= "\n";
		$post->bewerkt_tekst .= $bewerkt;
		$this->entityManager->persist($post);
		$this->entityManager->flush();

		if ($gelijkheid < 90) {
			$draad = $post->draad;
			$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
			$draad->laatste_post_id = $post->post_id;
			$draad->laatste_wijziging_uid = $post->uid;
			$rowCount = $this->forumDradenRepository->update($draad);
			if ($rowCount !== 1) {
				throw new CsrException('Bewerken mislukt');
			}
		}
	}

	public function resetLastPost(ForumDraad $draad)
	{
		// reset last post
		$last_post = $this->forumPostsRepository->findBy(
			[
				'draad_id' => $draad->draad_id,
				'wacht_goedkeuring' => false,
				'verwijderd' => false,
			],
			['laatst_gewijzigd' => 'DESC']
		)[0];
		if ($last_post) {
			$draad->laatste_post_id = $last_post->post_id;
			$draad->laatste_wijziging_uid = $last_post->uid;
			$draad->laatst_gewijzigd = $last_post->laatst_gewijzigd;
		} else {
			$draad->laatste_post_id = null;
			$draad->laatste_wijziging_uid = null;
			$draad->laatst_gewijzigd = null;
			$draad->verwijderd = true;
			MeldingUtil::setMelding(
				'Enige bericht in draad verwijderd: draad ook verwijderd',
				2
			);
		}
		$this->entityManager->persist($draad);
		$this->entityManager->flush();
	}

	public function goedkeurenForumPost(ForumPost $post)
	{
		if ($post->wacht_goedkeuring) {
			$post->wacht_goedkeuring = false;
			$post->laatst_gewijzigd = date_create_immutable();
			$post->bewerkt_tekst .=
				'[prive=P_FORUM_MOD]Goedgekeurd door [lid=' .
				LoginService::getUid() .
				'] [reldate]' .
				DateUtil::dateFormatIntl(
					$post->laatst_gewijzigd,
					DateUtil::DATETIME_FORMAT
				) .
				'[/reldate][/prive]' .
				"\n";
			$this->entityManager->persist($post);
			$this->entityManager->flush();
		}
		$draad = $post->draad;
		$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
		$draad->laatste_post_id = $post->post_id;
		$draad->laatste_wijziging_uid = $post->uid;
		if ($draad->wacht_goedkeuring) {
			$draad->wacht_goedkeuring = false;
			$this->forumMeldingenService->stuurDeelMeldingen($post);
		}
		$this->forumDradenRepository->update($draad);
	}
}
