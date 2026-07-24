<?php

namespace CsrDelft\model\entity;

use CsrDelft\common\EnumTrait;

/**
 */
enum LidStatus: string
{
	use EnumTrait;
	/**
	 * Status voor h.t. leden.
	 */
	case Noviet = 'S_NOVIET';
	case Lid = 'S_LID';
	case Gastlid = 'S_GASTLID';

	/**
	 * Status voor o.t. leden.
	 */
	case Oudlid = 'S_OUDLID';
	case Erelid = 'S_ERELID';

	/**
	 * Status voor niet-leden.
	 */
	case Overleden = 'S_OVERLEDEN';
	case Exlid = 'S_EXLID';
	case Nobody = 'S_NOBODY';
	case Commissie = 'S_CIE';
	case Kringel = 'S_KRINGEL';

	public function getDescription(): string {
		return match($this) {
			self::Noviet => 'Noviet',
			self::Lid => 'Lid',
			self::Gastlid => 'Gastlid',
			self::Oudlid => 'Oudlid',
			self::Erelid => 'Erelid',
			self::Overleden => 'Overleden',
			self::Exlid => 'Ex-lid',
			self::Nobody => 'Nobody',
			self::Commissie => 'Commissie (LDAP)',
			self::Kringel => 'Kringel',
		};
	}

	public function getChar(): string {
		return match($this) {
			self::Noviet => '',
			self::Lid => '',
			self::Gastlid => '',
			self::Commissie => '∈',
			self::Exlid => '∉',
			self::Nobody => '∉',
			self::Kringel => '~',
			self::Oudlid => '•',
			self::Erelid => '☀',
			self::Overleden => '✝',
		};
	}

	/**
	 * @return bool
	 */
	public function isLidLike(): bool
	{
		return match($this) {
			self::Noviet, self::Lid, self::Gastlid => true,
			default => false
		};
	}
	/**
	 * @return LidStatus[]
	 */
	public static function getLidLike(): array
	{
		return [self::Noviet, self::Lid, self::Gastlid];
	}

	public function isFiscaalLidLike(): bool {
		return match($this) {
			self::Noviet, self::Lid, self::Gastlid, self::Kringel => true,
			default => false
		};
	}

	public function isFiscaalOudlidLike(): bool {
		return match($this) {
			self::Oudlid, self::Erelid, self::Exlid, self::Nobody => true,
			default => false
		};
	}

	/**
	 * @return bool
	 */
	public function isOudlidLike(): bool
	{
		return match($this) {
			self::Oudlid, self::Erelid => true,
			default => false
		};
	}

	public function getValue(): string {
		return $this->value;
	}

}
