<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\common\Mail;
use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\MailService;
use CsrDelft\service\security\SuService;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;

/**
 * @method Declaratie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Declaratie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Declaratie[]    findAll()
 * @method Declaratie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieRepository extends AbstractRepository
{
	public function __construct(
		ManagerRegistry $registry,
		private readonly SuService $suService,
		private readonly Environment $twig,
		private readonly MailService $mailService
	) {
		parent::__construct($registry, Declaratie::class);
	}

	public function verwijderen(Declaratie $declaratie)
	{
		foreach ($declaratie->getBonnen() as $bon) {
			foreach ($bon->getRegels() as $regel) {
				$this->remove($regel);
			}
			$this->getEntityManager()->flush();
			$this->remove($bon);
		}
		$this->getEntityManager()->flush();
		$this->remove($declaratie);
		$this->getEntityManager()->flush();
	}

	public function mijnDeclaraties(Profiel $profiel)
	{
		return array_filter(
			$this->findBy(
				[
					'indiener' => $profiel,
				],
				['id' => 'desc']
			),
			fn($decl) => $decl->magBekijken()
		);
	}

	public function stuurMail(Declaratie $declaratie)
	{
		$wachtrij = $declaratie->getCategorie()->getWachtrij();

		if (!empty($wachtrij->getEmail())) {
			$bericht = $this->twig->render('declaratie/mail.html.twig', [
				'declaratie' => $declaratie,
			]);

			$mail = new Mail(
				[$wachtrij->getEmail() => ''],
				"Declaratie van {$declaratie->getIndiener()->getNaam()} (#{$declaratie->getId()})",
				$bericht
			);
			$mail->setReplyTo(
				$declaratie->getIndiener()->getPrimaryEmail(),
				$declaratie->getIndiener()->getNaam()
			);
			$this->mailService->send($mail);
		}
	}
}
