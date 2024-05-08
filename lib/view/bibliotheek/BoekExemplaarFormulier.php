<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\bibliotheek\BoekExemplaar;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BoekExemplaarFormulier implements FormulierTypeInterface
{
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @param FormulierBuilder $builder
	 * @param BoekExemplaar $data
	 * @param array $options
	 */
	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		$builder->setAction(
			$this->urlGenerator->generate('csrdelft_bibliotheek_exemplaar', [
				'exemplaar' => $data->id,
			])
		);
		$builder->setTitel('');

		$fields = [];
		$fields[] = new TextField('opmerking', $data->opmerking, 'Beschrijving:');
		$fields[] = new SubmitKnop();
		$builder->addFields($fields);
	}
}
