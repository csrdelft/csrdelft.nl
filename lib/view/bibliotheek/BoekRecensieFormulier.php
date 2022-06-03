<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\bibliotheek\BoekRecensie;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Boek weergeven
 */
class BoekRecensieFormulier implements FormulierTypeInterface
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
     * @param BoekRecensie $data
     * @param array $options
     */
    public function createFormulier(FormulierBuilder $builder, $data, $options = [])
    {
        $builder->setAction($this->urlGenerator->generate('csrdelft_bibliotheek_recensie', ['boek' => $data->boek->id]));
        $builder->setTitel('');
        $fields = [];
        $fields['beschrijving'] = new TextareaField("beschrijving", $data->beschrijving, null);

        $fields[] = new SubmitKnop();
        $builder->addFields($fields);
        $builder->addCssClass('boekformulier');
    }
}
