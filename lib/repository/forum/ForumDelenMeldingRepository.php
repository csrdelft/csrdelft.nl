<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\Mail;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDeelMelding;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\MailService;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;

/**
 * Model voor bijhouden, bewerken en verzenden van meldingen voor forumberichten in forumdelen
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 * @method ForumDeelMelding|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDeelMelding|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDeelMelding[]    findAll()
 * @method ForumDeelMelding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDelenMeldingRepository extends AbstractRepository
{
	/**
	 * @var SuService
	 */
	private $suService;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var MailService
	 */
	private $mailService;

	public function __construct(
		ManagerRegistry $registry,
		Environment $twig,
		SuService $suService,
		MailService $mailService
	) {
		parent::__construct($registry, ForumDeelMelding::class);
		$this->suService = $suService;
		$this->twig = $twig;
		$this->mailService = $mailService;
	}

	protected function maakForumDeelMelding(ForumDeel $deel, $uid)
	{
		$melding = new ForumDeelMelding();
		$melding->deel = $deel;
		$melding->forum_id = $deel->forum_id;
		$melding->uid = $uid;
		$this->getEntityManager()->persist($melding);
		$this->getEntityManager()->flush();
		return $melding;
	}

	/**
	 * Past gewenste meldingsactie toe voor gegeven lid.
	 *
	 * Als lid wil volgen, maar lid volgt op dit moment nog niet, activeer volgen.
	 * Als lid niet wil volgen, maar lid volgt op dit moment wel, deactiveer volgen.
	 * Anders, doe niets.
	 * @param ForumDeel $deel
	 * @param bool $actief of lid meldingen wil ontvangen
	 * @param string $uid uid van lid, standaard huidig ingelogd lid
	 */
	public function setMeldingVoorLid(ForumDeel $deel, $actief, $uid = null)
	{
		if ($uid === null) {
			$uid = LoginService::getUid();
		}

		$lidWilMeldingVoorDeel = $deel->lidWilMeldingVoorDeel($uid);
		if ($lidWilMeldingVoorDeel && !$actief) {
			// Wil niet, heeft nog wel
			$melding = $this->find(['forum_id' => $deel->forum_id, 'uid' => $uid]);
			$this->getEntityManager()->remove($melding);
			$this->getEntityManager()->flush();
		} elseif (!$lidWilMeldingVoorDeel && $actief) {
			// Wil wel, heeft nog niet
			$this->maakForumDeelMelding($deel, $uid);
		}
	}

	/**
	 * Verwijder alle te ontvangen meldingen voor gegeven lid
	 * @param $uids
	 */
	public function stopAlleMeldingenVoorLeden($uids)
	{
		$this->createQueryBuilder('fdm')
			->delete()
			->where('fdm.uid in (:uids)')
			->setParameter('uids', $uids)
			->getQuery()
			->execute();
	}

	/**
	 * Verwijder alle te ontvangen meldingen voor gegeven forumdeel.
	 * @param ForumDeel|int $deel
	 */
	public function stopMeldingenVoorIedereen($deel)
	{
		$id = $deel instanceof ForumDeel ? $deel->forum_id : $deel;
		$manager = $this->getEntityManager();
		foreach ($this->findBy(['forum_id' => $id]) as $melding) {
			$manager->remove($melding);
		}
		$manager->flush();
	}
}
