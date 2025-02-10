<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrException;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\security\AccountRepository;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * RBAC met MAC en DAC implementatie.
 *
 * @see http://csrc.nist.gov/groups/SNS/rbac/faq.html
 */
class AccessService
{
	const PREFIX_ACTIVITEIT = 'ACTIVITEIT';
	const PREFIX_BESTUUR = 'BESTUUR';
	const PREFIX_COMMISSIE = 'COMMISSIE';
	const PREFIX_GROEP = 'GROEP';
	const PREFIX_KETZER = 'KETZER';
	const PREFIX_ONDERVERENIGING = 'ONDERVERENIGING';
	const PREFIX_WERKGROEP = 'WERKGROEP';
	const PREFIX_WOONOORD = 'WOONOORD';
	const PREFIX_VERTICALE = 'VERTICALE';
	const PREFIX_KRING = 'KRING';
	const PREFIX_GESLACHT = 'GESLACHT';
	const PREFIX_STATUS = 'STATUS';
	const PREFIX_LICHTING = 'LICHTING';
	const PREFIX_LIDJAAR = 'LIDJAAR';
	const PREFIX_OUDEREJAARS = 'OUDEREJAARS';
	const PREFIX_EERSTEJAARS = 'EERSTEJAARS';
	const PREFIX_MAALTIJD = 'MAALTIJD';
	const PREFIX_KWALIFICATIE = 'KWALIFICATIE';

	/**
	 * Geldige prefixes voor rechten
	 * @var array
	 */
	private static $prefix = [
		self::PREFIX_ACTIVITEIT,
		self::PREFIX_BESTUUR,
		self::PREFIX_COMMISSIE,
		self::PREFIX_GROEP,
		self::PREFIX_KETZER,
		self::PREFIX_ONDERVERENIGING,
		self::PREFIX_WERKGROEP,
		self::PREFIX_WOONOORD,
		self::PREFIX_VERTICALE,
		self::PREFIX_KRING,
		self::PREFIX_GESLACHT,
		self::PREFIX_STATUS,
		self::PREFIX_LICHTING,
		self::PREFIX_LIDJAAR,
		self::PREFIX_OUDEREJAARS,
		self::PREFIX_EERSTEJAARS,
		self::PREFIX_MAALTIJD,
		self::PREFIX_KWALIFICATIE,
	];

	/**
	 * @param AccessDecisionManagerInterface $accessDecisionManager
	 */
	public function __construct(
		private readonly AccessDecisionManagerInterface $accessDecisionManager
	) {
	}

	/**
	 * Controleert of een specifieke gebruiker de juiste rechten heeft.
	 *
	 * @param UserInterface $user
	 * @param $attribute
	 * @param $subject
	 * @return bool
	 */
	public function isUserGranted(
		UserInterface $user,
		$attribute,
		$subject = null
	) {
		$token = new UsernamePasswordToken(
			$user,
			'none',
			'none',
			$user->getRoles()
		);

		return $this->accessDecisionManager->decide($token, [$attribute], $subject);
	}

	/**
	 * @param string $lidstatus
	 *
	 * @return string
	 * @throws CsrException
	 */
	public function getDefaultPermissionRole($lidstatus)
	{
		return match ($lidstatus) {
			LidStatus::Kringel,
			LidStatus::Noviet,
			LidStatus::Lid,
			LidStatus::Gastlid
				=> AccessRole::Lid,
			LidStatus::Oudlid, LidStatus::Erelid => AccessRole::Oudlid,
			LidStatus::Commissie,
			LidStatus::Overleden,
			LidStatus::Exlid,
			LidStatus::Nobody
				=> AccessRole::Nobody,
			default => throw new CsrException('LidStatus onbekend'),
		};
	}

	/**
	 * @return string[]
	 */
	public function getPermissionSuggestions()
	{
		$suggestions = [];
		$suggestions[] = 'bestuur';
		$suggestions[] = 'geslacht:m';
		$suggestions[] = 'geslacht:v';
		$suggestions[] = 'ouderejaars';
		$suggestions[] = 'eerstejaars';
		return $suggestions;
	}

	/**
	 * Get error(s) in permission string, if any.
	 *
	 * @param string $permissions
	 * @return array empty if no errors; substring(s) of $permissions containing error(s) otherwise
	 */
	public function getPermissionStringErrors($permissions)
	{
		$errors = [];
		// OR
		$or = explode(',', $permissions);
		foreach ($or as $and) {
			// AND
			$and = explode('+', $and);
			foreach ($and as $or2) {
				// OR (secondary)
				$or2 = explode('|', $or2);
				foreach ($or2 as $perm) {
					if (!$this->isValidPermission($perm)) {
						$errors[] = $perm;
					}
				}
			}
		}
		return $errors;
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function isValidPermission($permission)
	{
		// case insensitive
		$permission = strtoupper($permission);

		// Is de gevraagde permissie het uid van de gevraagde gebruiker?
		if (AccountRepository::isValidUid(strtolower($permission))) {
			return true;
		}

		// Oude permissie
		if (preg_match('/P_[a-zA-Z_]+/', $permission)) {
			return false;
		}

		if (preg_match('/^ROLE_/', $permission)) {
			//Aannemen dat alle ROLE_ permissies kloppen
			return true;
		}

		// splits permissie in type, waarde en rol
		$p = explode(':', $permission);
		if (in_array($p[0], self::$prefix) && count($p) <= 3) {
			if (isset($p[1]) && $p[1] == '') {
				return false;
			}
			if (isset($p[2]) && $p[2] == '') {
				return false;
			}
			return true;
		}

		return false;
	}

	/**
	 * Converteer een oude permissie, zoals P_LOGGED_IN naar een nieuwe permissie zoals ROLE_LOGGED_IN.
	 *
	 * @param $permissie
	 * @return array|string|string[]|null
	 */
	public function converteerPermissie($permissie)
	{
		return preg_replace('/P_/', 'ROLE_', $permissie);
	}
}
