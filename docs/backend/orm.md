---
layout: default
parent: Backend
nav_order: 1
title: ORM
---

# ORM / Database Entities Maken

Voor interactie met de database wordt een ORM gebruikt, namelijk [Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/index.html). Dit ORM is goed geintegreerd met Symfony. De documentatie van Doctrine is best wel goed, dus kijk daar vooral een keer naar als je aan de slag gaat met het maken van entities.

## Een Entity maken

Met een Entity beschrijf je hoe een object eruit ziet dat je later in de database gaat opslaan. Dit is een normaal PHP class met een aantal extra toevoegingen. Entities zijn te vinden in de `lib/entities` map in de repository.

Hier onder vind je ene simpele entity met drie velden. We gebruiken _PHP annotaties_ om aan te geven hoe de velden er in de database uit zien.

```php
namespace CsrDelft\entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CsrDelft\repository\MijnEntityRepository")
 */
class MijnEntity {
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $waarde;
	/**
	 * @var string
	 * @ORM\Column(type="uid", nullable=true)
	 */
	public $uid;
}
```

Een entity is op zich al genoeg, maar het kan handig zijn om ook een Repository te maken

## Een Repository maken

Een Repository beschrijft verschillende acties die je met een entity kan doen, zoals er op zoeken of er een genereren.

Zie hier onder een simpele repository. Het belangrijkste stuk is de constructor. Daarnaast is het belangrijk om `CsrDelft\Repository\AbstractRepository` te extenden om de boel conistent te houden.

Een Repository is ook altijd een [Service](services.md), dus naast de dingen die in de constructor moeten staan kun je ook andere services hier in zetten. Let wel op dat je Repository alleen zaken moet regelen voor de specifieke Entity waar je hem voor maakt.

```php
namespace CsrDelft\repository;

use CsrDelft\entity\MijnEntity;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class MijnEntityRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MijnEntity::class);
    }

    public function zoek($query) {
        return $this->createQueryBuilder('e')
            ->where('e.waarde LIKE :query')
            ->setParameter('query', "%$query%")
            ->getQuery()->getResult();
    }

    public function nieuwEntity() {
        $entity = new MijnEntity();
        $entity->waarde = 'leeg';
        $entity->uid = 'x999';

        return $entity;
    }
}
```

## Een migratie maken

Kijk op de [Migraties](../deploy/migraties.md) pagina om te zien hoe je een database tabel kan maken voor je nieuwe entity.
