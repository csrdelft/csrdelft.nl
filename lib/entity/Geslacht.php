<?php

namespace CsrDelft\entity;

use CsrDelft\common\EnumTrait;

enum Geslacht: string {
	use EnumTrait;
	case Man = 'm';
	case Vrouw = 'v';

	public function getDescription(): string {
		return match($this) {
			Geslacht::Man => 'man',
			Geslacht::Vrouw => 'vrouw'
		};
	}

	public function getValue(): string {
		return $this->value;
	}

	public function isVrouw(): bool {
		return $this === self::Vrouw;
	}

	public function isMan(): bool {
		return $this === self::Man;
	}
}
