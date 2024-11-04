<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Kring;
use CsrDelft\repository\GroepRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\User\UserInterface;

class KringenRepository extends GroepRepository
{
	public function getEntityClassName(): string
	{
		return Kring::class;
	}

	/**
	 * @inheritDoc
	 * @return Kring[]
	 */
	public function findBy(
		array $criteria,
		array $orderBy = null,
		$limit = null,
		$offset = null
	): array {
		return parent::findBy(
			$criteria,
			['verticale' => 'ASC', 'kringNummer' => 'ASC'] + ($orderBy ?? []),
			$limit,
			$offset
		);
	}

	public function get($id)
	{
		if (is_numeric($id)) {
			return parent::get($id);
		}
		[$verticale, $kringNummer] = explode('.', (string) $id);
		return $this->findOneBy([
			'verticale' => $verticale,
			'kringNummer' => $kringNummer,
		]);
	}

	public function nieuw($letter = null)
	{
		/** @var Kring $kring */
		$kring = parent::nieuw();
		$kring->verticale = $letter;
		return $kring;
	}
}
