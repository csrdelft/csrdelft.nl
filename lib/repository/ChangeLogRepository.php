<?php

namespace CsrDelft\repository;

use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\entity\ChangeLogEntry;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * ChangeLogModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method ChangeLogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangeLogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangeLogEntry[]    findAll()
 * @method ChangeLogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangeLogRepository extends AbstractRepository
{
	/**
	 * @var SerializerInterface
	 */
	private $serializer;
	/**
	 * @var Security
	 */
	private $security;

	/**
	 * ChangeLogModel constructor.
	 * @param ManagerRegistry $registry
	 * @param SerializerInterface $serializer
	 * @param Security $security
	 */
	public function __construct(
		ManagerRegistry $registry,
		SerializerInterface $serializer,
		Security $security
	) {
		parent::__construct($registry, ChangeLogEntry::class);

		$this->serializer = $serializer;
		$this->security = $security;
	}

	/**
	 * @param string $subject
	 * @param string $property
	 * @param string $old
	 * @param string $new
	 *
	 * @return ChangeLogEntry
	 */
	public function log($subject, $property, $old, $new)
	{
		$change = $this->nieuw($subject, $property, $old, $new);
		$this->create($change);
		return $change;
	}

	public function serialize($value): string
	{
		return $this->serializer->serialize($value, 'json', ['groups' => 'log']);
	}

	/**
	 * @param string|mixed $subject
	 * @param string $property
	 * @param string $old
	 * @param string $new
	 *
	 * @return ChangeLogEntry
	 */
	public function nieuw($subject, $property, $old, $new): ChangeLogEntry
	{
		$change = new ChangeLogEntry();
		$change->moment = date_create_immutable();
		try {
			$meta = $this->_em->getClassMetadata(get_class($subject));
			$change->subject =
				implode('.', $meta->getIdentifierValues($subject)) .
				'@' .
				strtolower(ReflectionUtil::short_class(get_class($subject))) .
				'.csrdelft.nl';
		} catch (MappingException $ex) {
			// ignore
			$change->subject = $subject;
		}

		$change->property = $property;
		$change->old_value = $old;
		$change->new_value = $new;
		$token = $this->security->getToken();
		if ($token == null) {
			$change->uid = LoginService::UID_EXTERN;
		} elseif ($token instanceof SwitchUserToken) {
			$change->uid = $token->getOriginalToken()->getUsername();
		} else {
			$change->uid = $token->getUsername();
		}
		return $change;
	}

	/**
	 * @param ChangeLogEntry $change
	 * @return void
	 */
	public function create(ChangeLogEntry $change)
	{
		$this->getEntityManager()->persist($change);
		$this->getEntityManager()->flush();
	}

	/**
	 * @param ChangeLogEntry[] $diff
	 */
	public function logChanges(array $diff)
	{
		foreach ($diff as $change) {
			$this->create($change);
		}
	}
}
