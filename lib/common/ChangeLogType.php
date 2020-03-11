<?php


namespace CsrDelft\common;


use CsrDelft\entity\profiel\log\ProfielCreateLogGroup;
use CsrDelft\entity\profiel\log\ProfielLogCoveeTakenVerwijderChange;
use CsrDelft\entity\profiel\log\ProfielLogGroup;
use CsrDelft\entity\profiel\log\ProfielLogTextEntry;
use CsrDelft\entity\profiel\log\ProfielLogValueChange;
use CsrDelft\entity\profiel\log\ProfielLogValueChangeCensuur;
use CsrDelft\entity\profiel\log\ProfielLogVeldenVerwijderChange;
use CsrDelft\entity\profiel\log\ProfielUpdateLogGroup;
use CsrDelft\entity\profiel\log\UnparsedProfielLogGroup;
use CsrDelft\Orm\JsonSerializer\SafeJsonSerializer;
use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ChangeLogType extends Type {
	const ACCEPTED_TYPES = [
		ProfielLogGroup::class,
		ProfielCreateLogGroup::class,
		ProfielLogVeldenVerwijderChange::class,
		ProfielLogCoveeTakenVerwijderChange::class,
		ProfielLogTextEntry::class,
		ProfielLogValueChangeCensuur::class,
		ProfielLogValueChange::class,
		ProfielUpdateLogGroup::class,
		UnparsedProfielLogGroup::class,
		DateTime::class
	];
	/**
	 * @inheritDoc
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
		return 'TEXT';
	}
	public function convertToPHPValue($value, AbstractPlatform $platform) {
		$serializer = new SafeJsonSerializer(self::ACCEPTED_TYPES);
		return $serializer->unserialize($value);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {
		$serializer = new SafeJsonSerializer(self::ACCEPTED_TYPES);
		return $serializer->serialize($value);
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'changelog';
	}
}
