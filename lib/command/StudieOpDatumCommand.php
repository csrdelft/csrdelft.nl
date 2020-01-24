<?php

namespace CsrDelft\command;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\ProfielLogValueChange;
use CsrDelft\model\entity\profiel\ProfielUpdateLogGroup;
use CsrDelft\repository\ProfielRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class StudieOpDatumCommand extends Command {
	private $profielRepository;

	public function __construct(ProfielRepository $profielRepository) {
		$this->profielRepository = $profielRepository;

		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('leden:studie:datum')
			->setDescription('Haal de studies van leden op op een bepaalde datum.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$helper = $this->getHelper('question');

		// Bepaal datum
		$datum = null;
		do {
			$question = new Question("Per welke datum? [dd-mm-jjjj] ");
			$antwoord = $helper->ask($input, $output, $question);
			if ($antwoord) {
				$datum = \DateTime::createFromFormat('d-m-Y', $antwoord);
			}
		} while (!$datum);

		$output->writeln("");

		// Haal leden op
		/** @var Profiel $lid */
		foreach ($this->profielRepository->findAll() as $lid) {

			// Check of lid al lid was
			$lidVanaf = \DateTime::createFromFormat('d-m-Y', '01-09-' . $lid->lidjaar);
			if ($lidVanaf > $datum) continue;

			$watch = ['status', 'studie'];
			$values = [];
			foreach ($watch as $field) {
				$values[$field] = $lid->$field;
			}

			$logs = array_filter($lid->changelog, function($a) { return $a instanceof ProfielUpdateLogGroup; });
			usort($logs, function (ProfielUpdateLogGroup $a, ProfielUpdateLogGroup $b) {
				if ($a->timestamp == $b->timestamp) {
					return 0;
				}

				return $a->timestamp < $b->timestamp ? -1 : 1;
			});
			foreach ($logs as $log) {
				if (empty($watch)) break;
				foreach ($log->entries as $entry) {
					if ($entry instanceof ProfielLogValueChange) {
						if (in_array($entry->field, $watch)) {
							$value = $log->timestamp < $datum ? $entry->newValue : $entry->oldValue;
							$values[$entry->field] = $value;
							if ($log->timestamp > $datum) {
								$watch = array_diff($watch, [$entry->field]);
							}
						}
					}
				}
			}

			if (!LidStatus::isLidLike($values['status'])) continue;
			$output->writeln("{$lid->uid};{$lid->getNaam()};{$values['studie']}");
		}

		$output->writeln("");
	}
}
