<?php

namespace CsrDelft\common\Security\Voter\Entity;

use CsrDelft\common\CsrException;
use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\entity\fotoalbum\FotoTagAlbum;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class FotoAlbumVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const BEKIJKEN = 'bekijken';
	const VERWIJDEREN = 'verwijderen';
	const TOEVOEGEN = 'toevoegen';
	const AANPASSEN = 'aanpassen';
	const DOWNLOADEN = 'downloaden';
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security)
	{
		$this->security = $security;
	}

	public function supportsAttribute(string $attribute): bool
	{
		return in_array($attribute, [
			self::BEKIJKEN,
			self::VERWIJDEREN,
			self::TOEVOEGEN,
			self::AANPASSEN,
			self::DOWNLOADEN,
		]);
	}

	public function supportsType(string $subjectType): bool
	{
		return $subjectType == FotoAlbum::class;
	}

	/**
	 * @param string $attribute
	 * @param FotoAlbum $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		switch ($attribute) {
			case self::BEKIJKEN:
				if (
					!str_starts_with(
						realpath($subject->path),
						realpath(PHOTOALBUM_PATH . 'fotoalbum/')
					)
				) {
					return false;
				}
				if ($subject instanceof FotoTagAlbum) {
					return $this->security->isGranted('ROLE_LEDEN_READ');
				}
				if ($subject->isPubliek()) {
					return $this->security->isGranted('PUBLIC_ACCESS');
				} else {
					return $this->security->isGranted('ROLE_ALBUM_READ');
				}
			case self::VERWIJDEREN:
				if ($token->getUserIdentifier() == $subject->owner) {
					return true;
				}
				if ($subject->isPubliek()) {
					return $this->security->isGranted('ROLE_ALBUM_PUBLIC_DEL');
				} else {
					return $this->security->isGranted('ROLE_ALBUM_DEL');
				}
			case self::TOEVOEGEN:
				if ($subject->isPubliek()) {
					return $this->security->isGranted('ROLE_ALBUM_PUBLIC_ADD');
				} else {
					return $this->security->isGranted('ROLE_ALBUM_ADD');
				}
			case self::AANPASSEN:
				if ($subject->isPubliek()) {
					return $this->security->isGranted('ROLE_ALBUM_PUBLIC_MOD');
				} else {
					return $this->security->isGranted('ROLE_ALBUM_MOD') ||
						$token->getUserIdentifier() == $subject->owner;
				}
			case self::DOWNLOADEN:
				if ($subject->isPubliek()) {
					return $this->security->isGranted('ROLE_ALBUM_PUBLIC_DOWN');
				} else {
					return $this->security->isGranted('ROLE_ALBUM_DOWN');
				}
			default:
				throw new CsrException("Onbekende rechten nodig: '$attribute'.");
		}
	}
}
