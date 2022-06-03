<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Mail;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadMelding;
use CsrDelft\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\MailService;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;

/**
 * Model voor bijhouden, bewerken en verzenden van meldingen voor forumberichten
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 * @method ForumDraadMelding|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadMelding|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadMelding[]    findAll()
 * @method ForumDraadMelding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenMeldingRepository extends AbstractRepository
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
        parent::__construct($registry, ForumDraadMelding::class);
        $this->suService = $suService;
        $this->twig = $twig;
        $this->mailService = $mailService;
    }

    public function setNiveauVoorLid(ForumDraad $draad, ForumDraadMeldingNiveau $niveau)
    {
        $uid = LoginService::getUid();
        $voorkeur = $this->find(['draad_id' => $draad->draad_id, 'uid' => $uid]);
        if ($voorkeur) {
            $voorkeur->niveau = $niveau;
            $this->getEntityManager()->persist($voorkeur);
            $this->getEntityManager()->flush();
        } else {
            $this->maakForumDraadMelding($draad, $uid, $niveau);
        }
    }

    protected function maakForumDraadMelding(ForumDraad $draad, $uid, ForumDraadMeldingNiveau $niveau)
    {
        $melding = new ForumDraadMelding();
        $melding->draad = $draad;
        $melding->draad_id = $draad->draad_id;
        $melding->uid = $uid;
        $melding->niveau = $niveau;

        $this->getEntityManager()->persist($melding);
        $this->getEntityManager()->flush();
        return $melding;
    }

    public function stopAlleMeldingenVoorLeden(array $uids)
    {
        $this->createQueryBuilder('m')
            ->where('m.uid in (:uids)')
            ->setParameter('uids', $uids)
            ->delete()
            ->getQuery()->execute();
    }

    public function stopMeldingenVoorIedereen(array $draadIds)
    {
        $this->createQueryBuilder('m')
            ->where('m.draad_id in (:draad_ids)')
            ->setParameter('draad_ids', $draadIds)
            ->delete()
            ->getQuery()->execute();
    }

    public function stuurMeldingen(ForumPost $post)
    {
        $this->stuurMeldingenNaarVolgers($post);
        $this->stuurMeldingenNaarGenoemden($post);
    }

    /**
     * Stuurt meldingen van nieuw bericht naar leden met meldingsniveau op altijd
     *
     * @param ForumPost $post
     */
    public function stuurMeldingenNaarVolgers(ForumPost $post)
    {
        $auteur = ProfielRepository::get($post->uid);
        $draad = $post->draad;

        // Laad meldingsbericht in
        foreach ($this->getAltijdMeldingVoorDraad($draad) as $volger) {
            $volgerProfiel = ProfielRepository::get($volger->uid);

            // Stuur geen meldingen als lid niet gevonden is of lid de auteur
            if (!$volgerProfiel || $volgerProfiel->uid === $post->uid) {
                continue;
            }

            $account = $volgerProfiel->account;

            if (!$account) {
                $this->remove($volger);
            } else {
                $this->stuurMelding($account, $auteur, $post, $draad, 'mail/bericht/forumaltijdmelding.mail.twig');
            }
        }
    }

    public function getAltijdMeldingVoorDraad(ForumDraad $draad)
    {
        return $this->findBy(['draad_id' => $draad->draad_id, 'niveau' => ForumDraadMeldingNiveau::ALTIJD()]);
    }

    /**
     * Verzendt mail
     *
     * @param Account $ontvanger
     * @param Profiel $auteur
     * @param ForumPost $post
     * @param ForumDraad $draad
     * @param string $bericht
     */
    private function stuurMelding($ontvanger, $auteur, $post, $draad, $template)
    {

        // Stel huidig UID in op ontvanger om te voorkomen dat ontvanger privé of andere persoonlijke info te zien krijgt
        $this->suService->alsLid($ontvanger, function () use ($ontvanger, $auteur, $post, $draad, $template) {
            $bericht = $this->twig->render($template, [
                'naam' => $ontvanger->profiel->getNaam('civitas'),
                'auteur' => $auteur->getNaam('civitas'),
                'postlink' => $post->getLink(true),
                'titel' => $draad->titel,
                'tekst' => str_replace('\r\n', "\n", $post->tekst),
            ]);

            $mail = new Mail($ontvanger->profiel->getEmailOntvanger(), 'C.S.R. Forum: nieuwe reactie op ' . $draad->titel, $bericht);
            $this->mailService->send($mail);
        });
    }

    /**
     * Stuurt meldingen van nieuw bericht naar leden die genoemd / geciteerd worden in bericht
     *
     * @param ForumPost $post
     */
    public function stuurMeldingenNaarGenoemden(ForumPost $post)
    {
        $auteur = ProfielRepository::get($post->uid);
        $draad = $post->draad;

        // Laad meldingsbericht in
        $genoemden = $this->zoekGenoemdeLeden($post->tekst);
        foreach ($genoemden as $uid) {
            $genoemde = ProfielRepository::get($uid);

            // Stuur geen meldingen als lid niet gevonden is, lid de auteur is of als lid geen meldingen wil voor draadje
            // Met laatste voorwaarde worden ook leden afgevangen die sowieso al een melding zouden ontvangen
            if (!$genoemde || !$genoemde->account || $genoemde->uid === $post->uid || !ForumDraadMeldingNiveau::isVERMELDING($this->getNiveauVoorLid($draad, $genoemde->uid))) {
                continue;
            }

            $magMeldingKrijgen = $this->suService->alsLid($genoemde->account, function () use ($draad) {
                return $draad->magMeldingKrijgen();
            });

            if (!$magMeldingKrijgen) {
                continue;
            }

            $this->stuurMelding($genoemde->account, $auteur, $post, $draad, 'mail/bericht/forumvermeldingmelding.mail.twig');
        }
    }

    /**
     * Zoek genoemde leden in gegeven bericht
     *
     * @param string $bericht
     * @return string[]
     */
    public function zoekGenoemdeLeden($bericht)
    {
        $regex = "/\[(?:lid|citaat)=?\s*]?\s*([[:alnum:]]+)\s*(?:\]|\[)/";
        preg_match_all($regex, $bericht, $leden);

        return array_unique($leden[1]);
    }

    public function getNiveauVoorLid(ForumDraad $draad, $uid = null)
    {
        if ($uid === null) $uid = LoginService::getUid();

        $voorkeur = $this->find(['draad_id' => $draad->draad_id, 'uid' => $uid]);
        if ($voorkeur) {
            return $voorkeur->niveau;
        } else {
            $lidInstellingenRepository = ContainerFacade::getContainer()->get(LidInstellingenRepository::class);
            $wilMeldingBijVermelding = $lidInstellingenRepository->getInstellingVoorLid('forum', 'meldingStandaard', $uid);
            return $wilMeldingBijVermelding === 'ja' ? ForumDraadMeldingNiveau::VERMELDING() : ForumDraadMeldingNiveau::NOOIT();
        }
    }
}
