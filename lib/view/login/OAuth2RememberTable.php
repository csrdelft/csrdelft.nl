<?php

namespace CsrDelft\view\login;

use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\Component\DataTable\DataTableTypeInterface;
use CsrDelft\entity\security\RememberOAuth;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OAuth2RememberTable implements DataTableTypeInterface
{
	public function __construct(private readonly UrlGeneratorInterface $generator)
	{
	}

	public function createDataTable(
		DataTableBuilder $builder,
		array $options
	): void {
		$builder->setTitel('Verbonden externe applicaties');
		$builder->setBeschrijving(
			'In deze tabel vindt je applicaties die de stek gebruiken om in te loggen. Door een regel hier te verwijderen wordt je de volgende keer weer gevraagd of je wil inloggen.'
		);
		$builder->loadFromClass(RememberOAuth::class);
		$builder->setDataUrl(
			$this->generator->generate(
				'csrdelft_security_oauth2_oauth2remembertokendata'
			)
		);

		$builder->setColumnTitle('client_identifier', 'Applicatie');
		$builder->setColumnTitle('last_used', 'Laatst gebruikt op');
		$builder->setColumnTitle('remember_since', 'Aangemaakt op');

		$builder->hideColumn('uid');
		$builder->deleteColumn('scopes');

		$builder->addRowKnop(
			new DataTableRowKnop(
				$this->generator->generate(
					'csrdelft_security_oauth2_oauth2rememberdelete',
					['id' => ':id']
				),
				'Verwijderen',
				'verwijderen',
				'confirm'
			)
		);
	}
}
