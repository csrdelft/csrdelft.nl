<?php

namespace CsrDelft\view\login;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\UsernameField;
use CsrDelft\view\formulier\invoervelden\WachtwoordWijzigenField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\DeleteKnop;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AccountForm implements FormulierTypeInterface
{
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(
		Security $security,
		UrlGeneratorInterface $urlGenerator
	) {
		$this->security = $security;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @param FormulierBuilder $builder
	 * @param Account $data
	 * @param array $options
	 */
	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		$builder->setTitel('Inloggegevens aanpassen');
		$fields = [];

		$user = $this->security->getUser();

		if (LoginService::mag(P_LEDEN_MOD)) {
			$roles = [];
			foreach (AccessRole::canChangeAccessRoleTo($user->perm_role) as $optie) {
				$roles[$optie] = AccessRole::from($optie)->getDescription();
			}
			$fields[] = new SelectField(
				'perm_role',
				$data->perm_role,
				'Rechten',
				$roles
			);
		}

		$fields[] = new UsernameField('username', $data->username);
		$fields[] = new RequiredEmailField('email', $data->email, 'E-mailadres');
		$fields[] = new WachtwoordWijzigenField(
			'pass_plain',
			$data,
			!LoginService::mag(P_LEDEN_MOD)
		);

		$builder->addFields($fields);

		$knoppen = new FormDefaultKnoppen(
			$this->urlGenerator->generate('csrdelft_profiel_profiel', [
				'uid' => $data->uid,
			]),
			false,
			true,
			true,
			true
		);
		$delete = new DeleteKnop(
			$this->urlGenerator->generate('csrdelft_account_verwijderen', [
				'uid' => $data->uid,
			])
		);

		$knoppen->addKnop($delete, true);
		$builder->setFormKnoppen($knoppen);
	}
}
