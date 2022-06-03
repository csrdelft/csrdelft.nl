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

    public function __construct(ManagerRegistry $registry, Environment $twig, SuService $suService, MailService $mailService)
    {
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
        if ($uid === null) $uid = LoginService::getUid();

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
            ->getQuery()->execute();
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

    /**
     * Stuur alle meldingen rondom forumdelen.
     * @param ForumPost $post
     */
    public function stuurMeldingen(ForumPost $post)
    {
        $this->stuurMeldingenNaarVolgers($post);
    }

    /**
     * Verzendt mail
     *
     * @param Account $ontvanger
     * @param Profiel $auteur
     * @param ForumPost $post
     * @param ForumDraad $draad
     * @param ForumDeel $deel
     */
    private function stuurMelding(Account $ontvanger, $auteur, $post, $draad, $deel)
    {

        // Stel huidig UID in op ontvanger om te voorkomen dat ontvanger privé of andere persoonlijke info te zien krijgt
        $this->suService->alsLid($ontvanger, function () use ($draad, $deel, $ontvanger, $auteur, $post) {
            $bericht = $this->twig->render('mail/bericht/forumdeelmelding.mail.twig', [
                'naam' => $ontvanger->profiel->getNaam('civitas'),
                'auteur' => $auteur->getNaam('civitas'),
                'postlink' => $post->getLink(true),
                'titel' => $draad->titel,
                'forumdeel' => $deel->titel,
                'tekst' => str_replace('\r\n', "\n", $post->tekst),
            ]);
            if ($draad->magMeldingKrijgen()) {
                $mail = new Mail($ontvanger->profiel->getEmailOntvanger(), 'C.S.R. Forum: nieuw draadje in ' . $deel->titel . ': ' . $draad->titel, $bericht);
                $this->mailService->send($mail);
            }
        });
    }

    /**
     * Stuurt meldingen van nieuw bericht naar leden die forumdeel volgen.
     *
     * @param ForumPost $post
     */
    public function stuurMeldingenNaarVolgers(ForumPost $post)
    {
        $auteur = ProfielRepository::get($post->uid);
        $draad = $post->draad;
        $deel = $draad->deel;

        foreach ($deel->meldingen as $volger) {
            $volgerProfiel = ProfielRepository::get($volger->uid);

            // Stuur geen meldingen als lid niet gevonden is of lid de auteur
            if (!$volgerProfiel || $volgerProfiel->uid === $post->uid) {
                continue;
            }

            $account = $volgerProfiel->account;

            // Als dit lid geen account meer heeft, volgt dit lid niet meer deze post
            if (!$account) {
                $this->remove($volger);
            } else {
                $this->stuurMelding($account, $auteur, $post, $draad, $deel);
            }
        }
    }
}
