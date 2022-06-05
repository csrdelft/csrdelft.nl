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
	 * @var BbToProsemirror
	 */
	private $bbToProsemirror;
	/**
	 * @var ProsemirrorToBb
	 */
	private $prosemirrorToBb;

	public function __construct(
		BbToProsemirror $bbToProsemirror,
		ProsemirrorToBb $prosemirrorToBb
	) {
		$this->bbToProsemirror = $bbToProsemirror;
		$this->prosemirrorToBb = $prosemirrorToBb;
	}

	public function getParent()
	{
		return TextareaType::class;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addModelTransformer(
			new CallbackTransformer(
				function ($bbcode) {
					return json_encode(
						$this->bbToProsemirror->toProseMirror($bbcode),
						JSON_HEX_QUOT
					);
				},
				function ($data) {
					return $this->prosemirrorToBb->convertToBb(json_decode($data));
				}
			)
		);
	}
}
