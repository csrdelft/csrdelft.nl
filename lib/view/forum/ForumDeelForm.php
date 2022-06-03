<?php

namespace CsrDelft\view\forum;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\forum\ForumCategorie;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\DoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\DeleteKnop;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForumDeelForm implements FormulierTypeInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param ForumCategorieRepository $forumCategorieRepository
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param FormulierBuilder $builder
     * @param ForumDeel $data
     * @param array $options
     */
    public function createFormulier(FormulierBuilder $builder, $data, $options = [])
    {
        $aanmaken = $options['aanmaken'];
        $builder->setTitel('Deelforum ' . $aanmaken ? 'aanmaken' : 'beheren');
        $builder->addCssClass('ReloadPage PreventUnchanged');

        $fields = [];
        $fields[] = new DoctrineEntityField('categorie', $data->categorie, 'Categorie', ForumCategorie::class, $this->urlGenerator->generate('csrdelft_forum_forumcategoriesuggestie') . "?q=");
        $fields[] = new RequiredTextField('titel', $data->titel, 'Titel');
        $fields[] = new TextareaField('omschrijving', $data->omschrijving, 'Omschrijving');
        $fields[] = new RechtenField('rechten_lezen', $data->rechten_lezen, 'Lees-rechten');
        $fields[] = new RechtenField('rechten_posten', $data->rechten_posten, 'Post-rechten');
        $fields[] = new RechtenField('rechten_modereren', $data->rechten_modereren, 'Mod-rechten');
        $fields[] = new IntField('volgorde', $data->volgorde, 'Volgorde');

        $builder->addFields($fields);

        $formKnoppen = new FormDefaultKnoppen();

        if (!$aanmaken) {
            $delete = new DeleteKnop($this->urlGenerator->generate('csrdelft_forum_opheffen', ['forum_id' => $data->forum_id]));
            $formKnoppen->addKnop($delete, true);
        }

        $builder->setFormKnoppen($formKnoppen);
    }
}
