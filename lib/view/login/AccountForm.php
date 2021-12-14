<?php

namespace CsrDelft\view\login;

use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\UsernameField;
use CsrDelft\view\formulier\invoervelden\WachtwoordWijzigenField;
use CsrDelft\view\formulier\knoppen\DeleteKnop;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class AccountForm extends AbstractType
{
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(Security $security, UrlGeneratorInterface $urlGenerator)
	{
		$this->security = $security;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{


		$builder->setTitel('Inloggegevens aanpassen');
		$fields = [];

		$user = $this->security->getUser();

		if (LoginService::mag(P_LEDEN_MOD)) {

			$roles = array();
			foreach (AccessRole::canChangeAccessRoleTo($user->perm_role) as $optie) {
				$roles[AccessRole::from($optie)->getDescription()] = $optie;
			}

			$builder->add('perm_role', ChoiceType::class, ['choices' => $roles]);
		}

		$builder
			->add('username', TextType::class)
			->add('email', EmailType::class)
			->add('pass_plain', PasswordType::class);

		$fields[] = new UsernameField('username', $data->username);
		$fields[] = new RequiredEmailField('email', $data->email, 'E-mailadres');
		$fields[] = new WachtwoordWijzigenField('pass_plain', $data, !LoginService::mag(P_LEDEN_MOD));

		$builder->addFields($fields);

		$knoppen = new FormDefaultKnoppen($this->urlGenerator->generate('csrdelft_profiel_profiel', ['uid' => $data->uid]), false, true, true, true);
		$delete = new DeleteKnop($this->urlGenerator->generate('csrdelft_account_verwijderen', ['uid' => $data->uid]));

		$knoppen->addKnop($delete, true);
		$builder->setFormKnoppen($knoppen);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(['data_class' => Account::class]);
	}


}
