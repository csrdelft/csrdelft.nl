<?php


namespace CsrDelft\common;


use CsrDelft\model\entity\profiel\ProfielCreateLogGroup;
use CsrDelft\model\entity\profiel\ProfielLogCoveeTakenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielLogGroup;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use CsrDelft\model\entity\profiel\ProfielLogValueChange;
use CsrDelft\model\entity\profiel\ProfielLogValueChangeCensuur;
use CsrDelft\model\entity\profiel\ProfielLogVeldenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielUpdateLogGroup;
use CsrDelft\model\entity\profiel\UnparsedProfielLogGroup;
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
