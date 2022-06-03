<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\model\entity\profiel\ProfielCreateLogGroup;
use CsrDelft\model\entity\profiel\ProfielLogCoveeTakenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielLogGroup;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use CsrDelft\model\entity\profiel\ProfielLogValueChange;
use CsrDelft\model\entity\profiel\ProfielLogValueChangeCensuur;
use CsrDelft\model\entity\profiel\ProfielLogVeldenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielUpdateLogGroup;
use CsrDelft\model\entity\profiel\UnparsedProfielLogGroup;
use DateTime;
use DateTimeImmutable;

class ChangeLogType extends SafeJsonType {
	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'changelog';
	}

	protected function getAcceptedTypes() {
		return [
			ProfielLogGroup::class,
			ProfielCreateLogGroup::class,
			ProfielLogVeldenVerwijderChange::class,
			ProfielLogCoveeTakenVerwijderChange::class,
			ProfielLogTextEntry::class,
			ProfielLogValueChangeCensuur::class,
			ProfielLogValueChange::class,
			ProfielUpdateLogGroup::class,
			UnparsedProfielLogGroup::class,
			DateTime::class,
			DateTimeImmutable::class
		];
	}
}
