<?php

namespace CsrDelft\Component\Form\Type;

use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\ProsemirrorToBb;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class BbTextType extends AbstractType
{


	/**
	 * @return string
	 *
	 * @psalm-return TextareaType::class
	 */
	public function getParent(): string
	{
		return TextareaType::class;
	}

	/**
	 * @return void
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addModelTransformer(
			new CallbackTransformer(
				fn($bbcode) => json_encode(
					$this->bbToProsemirror->toProseMirror($bbcode),
					JSON_HEX_QUOT
				),
				fn($data) => $this->prosemirrorToBb->convertToBb(
					json_decode((string) $data)
				)
			)
		);
	}
}
